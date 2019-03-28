<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\DependencyInjection;

use Felds\SizeStrToBytes\Exception\BadFormat;
use Felds\SizeStrToBytes\SizeStrToBytes;
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
                    ->isRequired()
                    ->info(
                        "The expiration for the upload URL".
                        "\nIt can be a number of seconds or a relative date string to be added to the current time"
                    )
                    ->example("1 day")
                ->end()
                ->scalarNode('max_size')
                    ->defaultNull()
                    ->validate()
                        ->ifTrue(function ($str) {
                            try {
                                SizeStrToBytes::convert($str);
                                return false;
                            } catch (BadFormat $err) {
                                return true;
                            }
                        })
                        ->thenInvalid("Invalid size format %s")
                    ->end()
                    ->info("Max size of the file to be uploaded (in bytes)")
                    ->example("2G # = 2 * 1024^3 = 2147483648")
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
