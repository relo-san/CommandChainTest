<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace Tests\ChainCommandBundle\Console\Output;

use ChainCommandBundle\Console\Output\ConsoleLoggedOutput;

/**
 * Test for logging output.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class ConsoleLoggedOutputTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test write method for logging messages.
     */
    public function testWrite()
    {
        $logger = $this->getMock('Symfony\Bridge\Monolog\Logger', ['log'], ['name' => 'console.chain']);
        $logger->expects(static::once())
            ->method('log')
            ->with(200, 'Command completed.');

        $output = new ConsoleLoggedOutput($logger, 16);
        $output->write('Command completed.');
    }
}
