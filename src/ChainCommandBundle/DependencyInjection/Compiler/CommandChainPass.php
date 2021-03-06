<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace ChainCommandBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Compiler for chain command configuration.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class CommandChainPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $chainServices = $container->findTaggedServiceIds('console.chain');

        $chains = [];

        foreach ($chainServices as $id => $tags) {
            $definition = $container->getDefinition($id);
            $commandClass = $container->getParameterBag()->resolveValue($definition->getClass());

            foreach ($tags as $tag) {
                if (empty($tag['member-of'])) {
                    throw new \InvalidArgumentException(sprintf(
                        'Tagged command chaining "%s" must have configured "member-of" attribute.',
                        $id
                    ));
                }

                $mainCommand = $tag['member-of'];
                $chains[$mainCommand][] = $commandClass;
            }
        }

        $definition = $container->getDefinition('chain_command.chain_manager');
        $definition->replaceArgument(0, $chains);
    }
}
