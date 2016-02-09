<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace Tests\ChainCommandBundle\Console\Event;

use ChainCommandBundle\Console\Event\ConsoleChainEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Test for chain event.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class ConsoleChainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for requesting chain members from event.
     */
    public function testGetChainMembers()
    {
        $event = new ConsoleChainEvent(
            new Command('bar:main'),
            new ArrayInput([]),
            new ConsoleOutput(),
            ['foo:second' => 'bar:main']
        );

        static::assertEquals(['foo:second' => 'bar:main'], $event->getChainMembers());
    }
}
