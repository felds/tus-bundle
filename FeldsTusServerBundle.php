<?php
declare(strict_types=1);

namespace Felds\TusServerBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FeldsTusServerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // add the doctrine mappings
        $mappings = [
            __DIR__ . '/Entity' => 'Felds\TusServerBundle\Entity',
        ];
        $pass = DoctrineOrmMappingsPass::createAnnotationMappingDriver(
            $mappings, array_keys($mappings)
        );
        $container->addCompilerPass($pass);
    }
}
