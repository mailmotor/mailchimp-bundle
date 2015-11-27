<?php

namespace MailMotor\Bundle\MailChimpBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mailmotor');

        $rootNode
            ->children()
                ->arrayNode('mailchimp')
                    ->children()
                        ->scalarNode('api_key')->isRequired()->end()
                        ->scalarNode('list_id')->isRequired()->end()
                    ->end()
                ->end() // mailchimp
            ->end()
        ;

        return $treeBuilder;
    }
}
