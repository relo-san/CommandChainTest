<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace ChainCommandBundle\EventListener;

use ChainCommandBundle\Console\ChainManager;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Command listener for manage command chaining.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class CommandListener
{
    /**
     * @var ChainManager
     */
    private $manager;

    /**
     * Constructor.
     *
     * @param   ChainManager    $manager
     */
    public function __construct(ChainManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Disabling runs members of chains and launching chains of commands.
     *
     * @param   ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        $commandName = $command->getName();
        $application = $command->getApplication();

        if (!$this->manager->isInitialized()) {
            $this->manager->init($application);
        }

        if ($this->manager->isMember($commandName)) {
            $event->disableCommand();
            $event->stopPropagation();

            $mainCommands = $this->manager->getMainCommands($commandName);

            $event->getOutput()->writeln(sprintf(
                '<error>Error: "%s" command is a member of %s command%s chain and cannot be executed on its own.</error>',
                $commandName,
                implode(', ', array_map(
                    function ($name) {
                        return '"' . $name . '"';
                    },
                    $mainCommands
                )),
                count($mainCommands) > 1 ? 's' : ''
            ));
        }

        if ($this->manager->hasChains($commandName)) {
            $this->manager->runChain($command, $event->getInput());

            $event->disableCommand();
            $event->stopPropagation();
        }
    }
}
