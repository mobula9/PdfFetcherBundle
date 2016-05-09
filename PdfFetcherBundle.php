<?php

namespace Kasifi\PdfFetcherBundle;

use Kasifi\PdfFetcherBundle\DependencyInjection\ProcessorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PdfFetcherBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ProcessorCompilerPass());
    }
}
