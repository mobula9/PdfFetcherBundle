<?php

namespace Kasifi\PdfFetcherBundle\Processor;

use Goutte\Client;
use Kasifi\PdfFetcherBundle\Event\CrawlFinishedEvent;
use Kasifi\PdfFetcherBundle\FetcherEvents;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Link;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Processor.
 */
abstract class AbstractProcessor implements LoggerAwareInterface, ProcessorInterface
{
    /** @var Client */
    protected $client;

    /** @var LoggerInterface */
    protected $logger;

    /** @var string[] */
    protected $documentUrls;

    /** @var array */
    private $storedDocuments;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {

        $this->eventDispatcher = $eventDispatcher;
    }

    public function getCronFrequency()
    {
        return '0 19 * * * *';
    }

    /**
     * @param Link $link
     *
     * @return string
     */
    public function downloadDocument(Link $link)
    {
        $this->logger->info('File download', ['url' => $link->getUri()]);
        $this->client->click($link);
        $content = $this->client->getResponse()->getContent();
        $this->client->back();

        return $content;
    }

    public function storeDocument($meta, $content)
    {
        $this->storedDocuments[] = [
            'meta'    => $meta,
            'content' => $content,
        ];
    }

    protected function initClient()
    {
        $this->client = new Client();
    }

    public function fetchDocuments()
    {
        $this->initClient();
        $this->logger->info('Start login');
        $successPage = $this->login();
        $this->logger->info('Logged in');
        $this->logger->info('Start crawl');
        $this->crawl($successPage);
        $this->logger->info('Crawl finished');
        $this->dispatchRetrievedEvent();
    }

    protected function dispatchRetrievedEvent()
    {
        $event = new CrawlFinishedEvent($this->getDocuments());
        $this->eventDispatcher->dispatch(FetcherEvents::CRAWL_FINISHED, $event);
    }

    /**
     * @return array
     */
    private function getDocuments()
    {
        return $this->storedDocuments;
    }
}
