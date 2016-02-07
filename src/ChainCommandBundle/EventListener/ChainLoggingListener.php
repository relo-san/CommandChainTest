<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace ChainCommandBundle\EventListener;

use ChainCommandBundle\Console\Event\ConsoleChainEvent;
use Psr\Log\LoggerInterface;

/**
 * Command listener for logging command chaining.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class ChainLoggingListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param   LoggerInterface             $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logging info about chain, master command and anoncing start of his execution.
     *
     * @param   ConsoleChainEvent   $event
     */
    public function onConsoleChainCommand(ConsoleChainEvent $event)
    {
        $commandName = $event->getCommand()->getName();
        $this->logger->log(200, sprintf(
            '%s is a master command of a command chain that has registered member commands',
            $commandName
        ));

        foreach ($event->getChainMembers() as $member => $parentCommand) {
            $this->logger->log(200, sprintf('%s registered as a member of %s command chain', $member, $parentCommand));
        }

        $this->logger->log(200, sprintf('Executing %s command itself first:', $commandName));
    }

    /**
     * Logging info about start of executing the block of members of chain.
     *
     * @param   ConsoleChainEvent   $event
     */
    public function onConsoleChainMembers(ConsoleChainEvent $event)
    {
        $this->logger->log(200, sprintf('Executing %s chain members:', $event->getCommand()->getName()));
    }

    /**
     * Logging info about successful execution of all commands from the chain.
     *
     * @param   ConsoleChainEvent   $event
     */
    public function onConsoleChainTerminate(ConsoleChainEvent $event)
    {
        $this->logger->log(200, sprintf('Execution of %s chain completed.', $event->getCommand()->getName()));
    }
}
