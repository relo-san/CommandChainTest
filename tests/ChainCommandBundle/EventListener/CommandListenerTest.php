<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace Tests\ChainCommandBundle\EventListener;

use BarBundle\Command\HiCommand;
use ChainCommandBundle\Console\Output\ConsoleLoggedOutput;
use ChainCommandBundle\EventListener\CommandListener;
use FooBundle\Command\HelloCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Test for command listener.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class CommandListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for processing run of chain member command.
     */
    public function testOnConsoleCommandDisableAndMessageAtMemberOwnExecution()
    {
        $logger = $this->getMock('Symfony\Bridge\Monolog\Logger', ['log'], ['name' => 'console.chain']);

        $manager = $this->getMock(
            'ChainCommandBundle\Console\ChainManager',
            ['isMember', 'getMainCommands', 'hasChains', 'init'],
            [['foo:hello' => [0 => 'BarBundle\Command\HiCommand']], new EventDispatcher(), $logger]
        );

        $manager->expects(static::atLeastOnce())
            ->method('isMember')
            ->will(static::returnValue(true));

        $manager->expects(static::atLeastOnce())
            ->method('getMainCommands')
            ->will(static::returnValue([0 => 'foo:hello']));

        $manager->expects(static::atLeastOnce())
            ->method('hasChains')
            ->will(static::returnValue(false));

        $listener = new CommandListener($manager);

        $application = new Application();
        $command = new HiCommand();
        $command->setApplication($application);

        $event = new ConsoleCommandEvent($command, new ArrayInput([]), new ConsoleLoggedOutput($logger, 16));

        // test message on stdout throw logger
        $logger->expects(static::atLeastOnce())
            ->method('log')
            ->with(200, 'Error: "bar:hi" command is a member of "foo:hello" command chain and cannot be executed on its own.');

        $listener->onConsoleCommand($event);

        static::assertEquals(false, $event->commandShouldRun());
    }

    /**
     * Test for processing run of chain master command.
     */
    public function testOnConsoleCommandMainCommandExecution()
    {
        $logger = $this->getMock('Symfony\Bridge\Monolog\Logger', ['log'], ['name' => 'console.chain']);

        $manager = $this->getMock(
            'ChainCommandBundle\Console\ChainManager',
            ['isMember', 'runChain', 'hasChains', 'init'],
            [['foo:hello' => [0 => 'BarBundle\Command\HiCommand']], new EventDispatcher(), $logger]
        );

        $application = new Application();
        $command = new HelloCommand();
        $command->setApplication($application);
        $input = new ArrayInput([]);

        $manager->expects(static::atLeastOnce())
            ->method('isMember')
            ->will(static::returnValue(false));

        $manager->expects(static::atLeastOnce())
            ->method('runChain')
            ->with($command, $input);

        $manager->expects(static::atLeastOnce())
            ->method('hasChains')
            ->will(static::returnValue(true));

        $listener = new CommandListener($manager);

        $event = new ConsoleCommandEvent($command, $input, new ConsoleOutput());

        $listener->onConsoleCommand($event);

        static::assertEquals(false, $event->commandShouldRun());
    }
}
