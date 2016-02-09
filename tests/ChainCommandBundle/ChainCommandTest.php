<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace Tests\ChainCommandBundle;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Functional test for chain command functionality.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class ChainCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    protected $application;

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

        $kernel = new \AppKernel('test', false);
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
        $kernel->boot();
        $kernel->getContainer()->set('chain_command.logger', $this->logger);
    }

    public function testRunChainSuccessfully()
    {
        $input = new ArrayInput(['command' => 'foo:hello', '--quiet' => true]);

        $this->logger->expects(static::exactly(7))
            ->method('log')
            ->withConsecutive(
                [200, 'foo:hello is a master command of a command chain that has registered member commands'],
                [200, 'bar:hi registered as a member of foo:hello command chain'],
                [200, 'Executing foo:hello command itself first:'],
                [200, 'Hello from Foo!'],
                [200, 'Executing foo:hello chain members:'],
                [200, 'Hi from Bar!'],
                [200, 'Execution of foo:hello chain completed.']
            );

        $this->application->run($input);
    }

    protected function tearDown()
    {
        $this->logger = null;
        $this->application = null;
    }
}
