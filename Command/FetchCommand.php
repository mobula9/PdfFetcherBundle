<?php
namespace Kasifi\PdfFetcherBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FetchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('pdf-fetcher:fetch')
            ->addArgument('processor', InputArgument::REQUIRED, 'The processor id to use')
            ->setDescription('Fetch a document');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Init
        $container = $this->getContainer();
        $fetcher = $container->get('kasifi_pdf_fetcher.fetcher');
        $processorId = $input->getArgument('processor');
        $fetcher->selectProcessor($processorId);
        $io = new SymfonyStyle($input, $output);

        $fetcher->fetchDocuments();
        $io->success('Document(s) fetched.');
    }
}
