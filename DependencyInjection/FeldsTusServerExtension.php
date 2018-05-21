<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class FeldsTusServerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $alias = $this->getAlias();
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter("{$alias}.entity_class", $config['entity_class']);
        $container->setParameter("{$alias}.expiration", $config['expiration']);

        // var_dump($config, $container);
        // die;
    }
}
