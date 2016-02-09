<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace Tests\ChainCommandBundle\EventListener;

use ChainCommandBundle\Console\Event\ConsoleChainEvent;
use ChainCommandBundle\EventListener\ChainLoggingListener;
use FooBundle\Command\HelloCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Test for chain logging listener.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class ChainLoggingListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->logger = $this->getMock('Symfony\Bridge\Monolog\Logger', ['log'], ['name' => 'console.chain']);
    }

    /**
     * Test for logging messages on console chain command event.
     */
    public function testOnConsoleChainCommandLogMessages()
    {
        $application = new Application();
        $command = new HelloCommand();
        $command->setApplication($application);

        $this->logger->expects(static::exactly(3))
            ->method('log')
            ->withConsecutive(
                [200, 'foo:hello is a master command of a command chain that has registered member commands'],
                [200, 'bar:hi registered as a member of foo:hello command chain'],
                [200, 'Executing foo:hello command itself first:']
            );

        $listener = new ChainLoggingListener($this->logger);

        $event = new ConsoleChainEvent(
            $command,
            new ArrayInput([]),
            new ConsoleOutput(),
            ['bar:hi' => 'foo:hello']
        );

        $listener->onConsoleChainCommand($event);
    }

    /**
     * Test for logging messages on console chain members event.
     */
    public function testOnConsoleChainMembersLogMessage()
    {
        $application = new Application();
        $command = new HelloCommand();
        $command->setApplication($application);

        $this->logger->expects(static::once())
            ->method('log')
            ->with(200, 'Executing foo:hello chain members:');

        $listener = new ChainLoggingListener($this->logger);

        $event = new ConsoleChainEvent(
            $command,
            new ArrayInput([]),
            new ConsoleOutput(),
            ['bar:hi' => 'foo:hello']
        );

        $listener->onConsoleChainMembers($event);
    }

    public function testOnConsoleChainTerminateLogMessage()
    {
        $application = new Application();
        $command = new HelloCommand();
        $command->setApplication($application);

        $this->logger->expects(static::once())
            ->method('log')
            ->with(200, 'Execution of foo:hello chain completed.');

        $listener = new ChainLoggingListener($this->logger);

        $event = new ConsoleChainEvent(
            $command,
            new ArrayInput([]),
            new ConsoleOutput(),
            ['bar:hi' => 'foo:hello']
        );

        $listener->onConsoleChainTerminate($event);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        $this->logger = null;
    }
}
