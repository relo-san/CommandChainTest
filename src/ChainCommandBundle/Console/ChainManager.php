<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace ChainCommandBundle\Console;

use ChainCommandBundle\Console\Event\ConsoleChainEvent;
use ChainCommandBundle\Console\Output\ConsoleLoggedOutput;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manager for chaining commands.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class ChainManager
{
    /**
     * @var array
     */
    private $chains;

    /**
     * @var array
     */
    private $members;

    /**
     * @var Application|null
     */
    private $application;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string|null
     */
    private $lastChain;

    /**
     * @var int|null
     */
    private $chainExitCode;

    /**
     * Constructor.
     *
     * @param   array                       $chains     Initial chains configuration.
     * @param   EventDispatcherInterface    $dispatcher
     * @param   LoggerInterface             $logger
     */
    public function __construct(array $chains, EventDispatcherInterface $dispatcher, LoggerInterface $logger)
    {
        $this->chains = $chains;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * Checking if chain manager initialized (returns TRUE) or not (returns FALSE).
     *
     * @return  bool
     */
    public function isInitialized(): bool
    {
        return null !== $this->application;
    }

    /**
     * Initialize manager.
     *
     * @param   Application $application
     */
    public function init(Application $application)
    {
        $this->application = $application;

        if ($this->chains) {
            $commandMap = array_flip(array_reverse(array_map(
                function ($command) {
                    return get_class($command);
                },
                $application->all()
            )));

            $chains = $this->chains;
            $members = [];
            foreach ($chains as $mainCommand => $hisMembers) {
                foreach ($hisMembers as $i => $memberClass) {
                    if (isset($commandMap[$memberClass])) {
                        $memberCommand = $commandMap[$memberClass];
                        $chains[$mainCommand][$i] = $memberCommand;
                        $members[$memberCommand][] = $mainCommand;
                    } else {
                        throw new \InvalidArgumentException(sprintf(
                            'Command from class "%s" registered as member of "%s" command, but not enabled or incorrect.',
                            $memberClass,
                            $mainCommand
                        ));
                    }
                }
            }
            $this->chains = $chains;
            $this->members = $members;
        }
    }

    /**
     * Checking if specified command is member of any other command (returns TRUE) or not (returns FALSE).
     *
     * @param   string  $commandName    Name of command.
     * @return  bool
     */
    public function isMember(string $commandName): bool
    {
        return isset($this->members[$commandName]);
    }

    /**
     * Returns array of names of main commands or empty array if specified command not member of any other command.
     *
     * @param   string  $commandName    Name of command.
     * @return  array
     */
    public function getMainCommands(string $commandName): array
    {
        return $this->members[$commandName] ?? [];
    }

    /**
     * Checking if specified command has a chains of commands (returns TRUE) or not (returns FALSE).
     *
     * @param   string  $commandName    Name of command.
     * @return  bool
     */
    public function hasChains(string $commandName): bool
    {
        return isset($this->chains[$commandName]);
    }

    /**
     * Returns array of members of chain or empty array if command not have any chained command.
     *
     * @param   string  $commandName    Name of command.
     * @return  array
     */
    public function getMembers(string $commandName): array
    {
        if (isset($this->chains[$commandName])) {
            $members = [];
            foreach ($this->chains[$commandName] as $member) {
                $members[$member] = $commandName;

                if (isset($this->chains[$member])) {
                    $this->addMembers($member, $members);
                }
            }

            return $members;
        }

        return [];
    }

    /**
     * Returns name of last launched main command.
     *
     * @return  string|null
     */
    public function getLastChain()
    {
        return $this->lastChain;
    }

    /**
     * Return exit code of last launched member command.
     *
     * @return  int|null
     */
    public function getChainExitCode()
    {
        return $this->chainExitCode;
    }

    /**
     * Launches chain of commands.
     *
     * @param   Command         $command
     * @param   InputInterface  $input
     */
    public function runChain(Command $command, InputInterface $input)
    {
        $commandName = $command->getName();
        $this->lastChain = $commandName;
        $members = $this->getMembers($commandName);

        $verbosity = ConsoleLoggedOutput::VERBOSITY_NORMAL;
        if ($input->getParameterOption('-q') || $input->getParameterOption('--quiet')) {
            $verbosity = ConsoleLoggedOutput::VERBOSITY_QUIET;
        } elseif ($input->getParameterOption('-v')) {
            $verbosity = ConsoleLoggedOutput::VERBOSITY_DEBUG;
        } elseif ($input->getParameterOption('-vv')) {
            $verbosity = ConsoleLoggedOutput::VERBOSITY_VERBOSE;
        } elseif ($input->getParameterOption('-vvv')) {
            $verbosity = ConsoleLoggedOutput::VERBOSITY_VERY_VERBOSE;
        }

        $output = new ConsoleLoggedOutput($this->logger, $verbosity);

        $event = new ConsoleChainEvent($command, $input, $output, $members);
        $this->dispatcher->dispatch(ChainConsoleEvents::COMMAND, $event);

        $this->chainExitCode = $this->doRun($command, $input, $output);

        $event = new ConsoleChainEvent($command, $input, $output, $members);
        $this->dispatcher->dispatch(ChainConsoleEvents::MEMBERS, $event);

        foreach ($members as $member => $parent) {
            $memberCommand = $this->application->find($member);
            $memberInput = new ArrayInput(['command' => $memberCommand->getName()]);

            $this->chainExitCode = $this->doRun($memberCommand, $memberInput, $output);
        }

        $event = new ConsoleChainEvent($command, $input, $output, $members);
        $this->dispatcher->dispatch(ChainConsoleEvents::TERMINATE, $event);
    }

    /**
     * Recursive adds members to $members from second levels of nested chain.
     *
     * @param   string  $commandName    Parent command in chain.
     * @param   array   $members        Array with members of chain.
     */
    private function addMembers(string $commandName, array &$members)
    {
        foreach ($this->chains[$commandName] as $member) {
            if (!isset($members[$member])) {
                $members[$member] = $commandName;
            }

            if (isset($this->chains[$member])) {
                $this->addMembers($member, $members);
            }
        }
    }

    /**
     * Executing command in chain.
     *
     * @param   Command         $command
     * @param   InputInterface  $input
     * @param   OutputInterface $output
     * @return  int|null
     * @throws  \Exception  Any exception that occurs during the execution of the command.
     */
    private function doRun(Command $command, InputInterface $input, OutputInterface $output)
    {
        try {
            $exitCode = $command->run($input, $output);
        } catch (\Exception $e) {
            $event = new ConsoleExceptionEvent($command, $input, $output, $e, $e->getCode());
            $this->dispatcher->dispatch(ConsoleEvents::EXCEPTION, $event);

            $e = $event->getException();

            $event = new ConsoleTerminateEvent($command, $input, $output, $e->getCode());
            $this->dispatcher->dispatch(ConsoleEvents::TERMINATE, $event);

            throw $e;
        }

        return $exitCode;
    }
}
