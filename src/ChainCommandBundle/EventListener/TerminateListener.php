<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace ChainCommandBundle\EventListener;

use ChainCommandBundle\Console\ChainManager;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

/**
 * Terminate listener for manage command chaining.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class TerminateListener
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
     * Update exit code after launch chain of commands.
     *
     * @param   ConsoleTerminateEvent   $event
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        if ($this->manager->getLastChain() === $event->getCommand()->getName()) {
            $event->setExitCode($this->manager->getChainExitCode());
        }
    }
}
