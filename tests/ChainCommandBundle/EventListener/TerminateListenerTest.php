<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace Tests\ChainCommandBundle\EventListener;

use ChainCommandBundle\EventListener\TerminateListener;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Test for terminate listener.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class TerminateListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test console terminate event.
     */
    public function testOnConsoleTerminate()
    {
        $logger = $this->getMock('Symfony\Bridge\Monolog\Logger', [], ['name' => 'console.chain']);
        $manager = $this->getMock(
            'ChainCommandBundle\Console\ChainManager',
            ['getLastChain', 'getChainExitCode'],
            [['foo:hello' => [0 => 'BarBundle\Command\HiCommand']], new EventDispatcher(), $logger]
        );

        $manager->expects(static::atLeastOnce())
            ->method('getLastChain')
            ->will(static::returnValue('foo:hello'));

        $manager->expects(static::atLeastOnce())
            ->method('getChainExitCode')
            ->will(static::returnValue(42));

        $listener = new TerminateListener($manager);

        $event = new ConsoleTerminateEvent(
            new Command('foo:hello'),
            new ArrayInput([]),
            new ConsoleOutput(),
            ConsoleCommandEvent::RETURN_CODE_DISABLED
        );

        $listener->onConsoleTerminate($event);

        static::assertEquals(42, $event->getExitCode());
    }
}
