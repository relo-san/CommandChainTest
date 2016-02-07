<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace FooBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Hello command.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class HelloCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('foo:hello');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello from Foo!');
    }
}
