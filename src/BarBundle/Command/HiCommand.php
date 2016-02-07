<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace BarBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Hi command.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class HiCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('bar:hi');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hi from Bar!');
    }
}
