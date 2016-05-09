<?php
namespace Kasifi\PdfFetcherBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FetchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('pdf-fetcher:fetch')
            ->setDescription('Fetch a document');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Init
        $container = $this->getContainer();
        $fetcher = $container->get('kasifi_pdf_fetcher.fetcher');
        $io = new SymfonyStyle($input, $output);

        $fetcher->fetch();
        $io->success('Fetched.');
    }
}
