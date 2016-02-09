<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace Tests\ChainCommandBundle\Console;

use BarBundle\Command\HiCommand;
use ChainCommandBundle\Console\ChainManager;
use FooBundle\Command\HelloCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Test for chain manager.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class ChainManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChainManager
     */
    protected $manager;

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
        $this->manager = new ChainManager(
            ['foo:hello' => [0 => 'BarBundle\Command\HiCommand']],
            new EventDispatcher(),
            $this->logger
        );
    }

    /**
     * Test case for missed or incorrect command.
     */
    public function testInitThrowAnExceptionIfTheCommandNotExist()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Command from class "BarBundle\Command\HiCommand" registered as member of "foo:hello" command, but not enabled or incorrect.'
        );

        $application = new Application();

        $this->manager->init($application);
    }

    /**
     * Test chain manager initialization.
     */
    public function testInit()
    {
        $application = new Application();

        $mainCommand = new HelloCommand();
        $mainCommand->setApplication($application);
        $application->add($mainCommand);

        $memberCommand = new HiCommand();
        $memberCommand->setApplication($application);
        $application->add($memberCommand);

        $this->manager->init($application);

        static::assertEquals(true, $this->manager->isInitialized());
    }

    /**
     * Test for isMember method.
     */
    public function testIsMember()
    {
        $application = new Application();

        $mainCommand = new HelloCommand();
        $mainCommand->setApplication($application);
        $application->add($mainCommand);

        $memberCommand = new HiCommand();
        $memberCommand->setApplication($application);
        $application->add($memberCommand);

        $this->manager->init($application);

        static::assertEquals(true, $this->manager->isMember('bar:hi'));

        static::assertEquals(false, $this->manager->isMember('foo:hello'));

        static::assertEquals(false, $this->manager->isMember('some:other'));
    }

    /**
     * Test for getMainCommands method.
     */
    public function testGetMainCommands()
    {
        $application = new Application();

        $mainCommand = new HelloCommand();
        $mainCommand->setApplication($application);
        $application->add($mainCommand);

        $memberCommand = new HiCommand();
        $memberCommand->setApplication($application);
        $application->add($memberCommand);

        $this->manager->init($application);

        static::assertEquals([0 => 'foo:hello'], $this->manager->getMainCommands('bar:hi'));

        static::assertEquals([], $this->manager->getMainCommands('foo:hello'));

        static::assertEquals([], $this->manager->getMainCommands('some:other'));
    }

    /**
     * Test for hasChains method.
     */
    public function testHasChains()
    {
        $application = new Application();

        $mainCommand = new HelloCommand();
        $mainCommand->setApplication($application);
        $application->add($mainCommand);

        $memberCommand = new HiCommand();
        $memberCommand->setApplication($application);
        $application->add($memberCommand);

        $this->manager->init($application);

        static::assertEquals(false, $this->manager->hasChains('bar:hi'));

        static::assertEquals(true, $this->manager->hasChains('foo:hello'));

        static::assertEquals(false, $this->manager->hasChains('some:other'));
    }

    /**
     * Test for getMembers method.
     */
    public function testGetMembers()
    {
        $application = new Application();

        $mainCommand = new HelloCommand();
        $mainCommand->setApplication($application);
        $application->add($mainCommand);

        $memberCommand = new HiCommand();
        $memberCommand->setApplication($application);
        $application->add($memberCommand);

        $this->manager->init($application);

        static::assertEquals([], $this->manager->getMembers('bar:hi'));

        static::assertEquals(['bar:hi' => 'foo:hello'], $this->manager->getMembers('foo:hello'));

        static::assertEquals([], $this->manager->getMembers('some:other'));
    }

    /**
     * Test for runChain method.
     */
    public function testRunChain()
    {
        $application = new Application();

        $mainCommand = new HelloCommand();
        $mainCommand->setApplication($application);
        $application->add($mainCommand);

        $memberCommand = new HiCommand();
        $memberCommand->setApplication($application);
        $application->add($memberCommand);

        $this->manager->init($application);

        $this->logger->expects(static::exactly(2))
            ->method('log')
            ->withConsecutive(
                [200, 'Hello from Foo!'],
                [200, 'Hi from Bar!']
            );

        $this->manager->runChain($mainCommand, new ArrayInput(['command' => 'foo:hello', '--quiet' => true]));
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        $this->manager = null;
    }
}
