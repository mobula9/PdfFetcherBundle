<?php

namespace Kasifi\PdfFetcherBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ProcessorCompilerPass.
 */
class ProcessorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('kasifi_pdf_fetcher.fetcher')) {
            return;
        }

        $definition = $container->findDefinition('kasifi_pdf_fetcher.fetcher');

        $taggedServices = $container->findTaggedServiceIds('kasifi_pdf_fetcher.fetch_processor');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addAvailableProcessor', [new Reference($id)]);
        }
    }
}
