<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class FeldsTusServerExtension extends Extension
{
    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $alias = $this->getAlias();
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // put the configured values in named parameters
        $container->setParameter("{$alias}.entity_class", $config['entity_class']);
        $container->setParameter("{$alias}.expires_in", $config['expires_in']);
        $container->setParameter("{$alias}.max_size", $config['max_size']);

        // load the services
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }
}
