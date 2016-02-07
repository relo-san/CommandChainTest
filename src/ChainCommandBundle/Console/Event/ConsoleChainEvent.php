<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace ChainCommandBundle\Console\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Event for do things on various points of executing chain of commands.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class ConsoleChainEvent extends ConsoleEvent
{
    /**
     * @var array
     */
    private $chain;

    /**
     * Constructor.
     *
     * @param   Command         $command
     * @param   InputInterface  $input
     * @param   OutputInterface $output
     * @param   array           $chain      All members of this chain.
     */
    public function __construct(Command $command, InputInterface $input, OutputInterface $output, array $chain)
    {
        parent::__construct($command, $input, $output);
        $this->chain = $chain;
    }

    /**
     * Returns all members of this chain.
     *
     * @return  array
     */
    public function getChainMembers(): array
    {
        return $this->chain;
    }
}
