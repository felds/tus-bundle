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
                    ->cannotBeEmpty()
                    ->info("Your entity class. It must extend Felds\\TusServerBundle\\Model\\AbstractUpload")
                    ->example("App\\Entity\\Upload")
                ->end()
                ->scalarNode('expires_in')
                    ->defaultNull()
                    ->info(
                        "Default expiration for the upload URL".
                        "\nIt can be a number of seconds or a relative date string to be added to the current time"
                    )
                    ->example("1 day")
                ->end()
                ->scalarNode('max_size')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info("Max size of the file to be uploaded (in bytes)")
                    ->example("2GB # = 1024^3 * 2 = 2147483648")
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
