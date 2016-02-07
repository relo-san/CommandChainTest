<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace ChainCommandBundle;

use ChainCommandBundle\DependencyInjection\Compiler\CommandChainPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * ChainCommand bundle ident.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class ChainCommandBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CommandChainPass());
    }
}
