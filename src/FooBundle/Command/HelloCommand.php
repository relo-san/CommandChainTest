<?php
declare(strict_types = 1);

/**
 * Test project.
 */

namespace FooBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Hello command.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class HelloCommand extends ContainerAwareCommand
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
