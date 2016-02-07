<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace ChainCommandBundle\Console;

/**
 * Events, dispatched throw command chain launching.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
final class ChainConsoleEvents
{
    /**
     * Event COMMAND occurs before launch main command of chain. Event listener method receives a
     * ChainCommandBundle\Console\Event\ConsoleChainEvent instance.
     *
     * @Event
     *
     * @var string
     */
    const COMMAND = 'console.chain.command';

    /**
     * Event MEMBERS occurs after execute main command and before launch first of members commands. Event listener
     * method receives a ChainCommandBundle\Console\Event\ConsoleChainEvent instance.
     *
     * @Event
     *
     * @var string
     */
    const MEMBERS = 'console.chain.members';

    /**
     * Event TERMINATE occurs after execute last of members command of chain. Event listener method receives a
     * ChainCommandBundle\Console\Event\ConsoleChainEvent instance.
     *
     * @Event
     *
     * @var string
     */
    const TERMINATE = 'console.chain.terminate';
}
