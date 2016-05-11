<?php

namespace Kasifi\PdfFetcherBundle\Processor;

use Goutte\Client;
use Kasifi\PdfFetcherBundle\Event\CrawlFinishedEvent;
use Kasifi\PdfFetcherBundle\FetcherEvents;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\BrowserKit\Response;
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
        $url = $link->getUri();
        $this->logger->info('File download', ['url' => $url]);
        $this->client->click($link);
        /** @var Response $lastResponse */
        $lastResponse = $this->client->getResponse();
        $headers = $lastResponse->getHeaders();

        $filename = $this->extractFilenameFromHeaders($headers);
        $status = $lastResponse->getStatus();
        $content = $lastResponse->getContent();
        $this->client->back();

        return [
            'url'      => $url,
            'status'   => $status,
            'headers'  => $headers,
            'filename' => $filename,
            'content'  => $content,
        ];
    }

    public function storeDocument($document)
    {
        $this->storedDocuments[] = $document;
    }

    protected function initClient()
    {
        $this->client = new Client();
        $this->client->setHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.86 Safari/537.36');
        $this->client->setHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8');
        $this->client->setHeader('Accept-Encoding', 'gzip, deflate, sdch');
        $this->client->setHeader('Accept-Language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
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
        $event = new CrawlFinishedEvent($this->getId(), $this->getDocuments());
        $this->eventDispatcher->dispatch(FetcherEvents::CRAWL_FINISHED, $event);
    }

    /**
     * @return array
     */
    private function getDocuments()
    {
        return $this->storedDocuments;
    }

    /**
     * @param $headers
     *
     * @return string
     */
    protected function extractFilenameFromHeaders($headers)
    {
        if (isset($headers['Content-Disposition'])) {
            $contentDisposition = end($headers['Content-Disposition']);
            $items = array_map('trim', explode(';', $contentDisposition));
            $filename = null;
            foreach ($items as $item) {
                $elements = array_map('trim', explode('=', $item));
                if (2 == count($elements)) {
                    list($key, $value) = $elements;
                    if ('filename' == strtolower($key)) {
                        $filename = $value;
                    }
                }
            }

            return trim($filename, ' "\'');
        }

        return null;
    }
}
