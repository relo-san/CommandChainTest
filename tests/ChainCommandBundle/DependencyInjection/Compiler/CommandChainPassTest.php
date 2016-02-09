<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace Tests\ChainCommandBundle\DependencyInjection\Compiler;

use ChainCommandBundle\DependencyInjection\Compiler\CommandChainPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Test for chain pass compiler.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class CommandChainPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for process method.
     */
    public function testProcess()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder', ['findTaggedServiceIds']);

        $container->expects(static::atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will(static::returnValue(['foo.some.command' => [0 => ['member-of' => 'bar:main']]]));

        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition', ['getClass']);
        $definition->expects(static::atLeastOnce())
            ->method('getClass')
            ->will(static::returnValue('Foo\Command\OtherCommand'));
        $container->setDefinition('foo.some.command', $definition);

        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $definition->expects(static::atLeastOnce())
            ->method('replaceArgument')
            ->with(0, ['bar:main' => [0 => 'Foo\Command\OtherCommand']]);
        $container->setDefinition('chain_command.chain_manager', $definition);

        $pass = new CommandChainPass();
        $pass->process($container);
    }

    /**
     * Test exception if tag not have "member-of" attribute.
     */
    public function testProcessThrowAnExceptionIfTheTagIsNotHaveAttributeMemberOf()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Tagged command chaining "foo.some.command" must have configured "member-of" attribute.'
        );

        $container = new ContainerBuilder();
        $container->addCompilerPass(new CommandChainPass());

        $definition = new Definition('FooBundle\Command\SomeCommand');
        $definition->addTag('console.chain');
        $container->setDefinition('foo.some.command', $definition);

        $container->compile();
    }

    /**
     * Test case for none of chains configured.
     */
    public function testThatChainsCanBeMissing()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder', ['findTaggedServiceIds']);

        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $definition->expects(static::atLeastOnce())
            ->method('replaceArgument')
            ->with(0, []);

        $container->setDefinition('chain_command.chain_manager', $definition);

        $container->expects(static::atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will(static::returnValue([]));

        $pass = new CommandChainPass();
        $pass->process($container);
    }
}
