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
        // $namespaces = ['Felds\\TusServerBundle\\Entity'];
        // $directories = [__DIR__ . '/Entity'];
        // $container->addCompilerPass(DoctrineOrmMappingsPass::createAnnotationMappingDriver($namespaces, $directories));
    }
}
