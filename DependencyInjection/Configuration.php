<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('felds_tus_server');

        $rootNode
            ->children()
                ->scalarNode('entity_class')
                    ->isRequired()
                    ->info("Your entity class. It must extend Felds\\TusServerBundle\\Model\\AbstractUpload")
                    ->example("App\\Entity\\Upload")
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
