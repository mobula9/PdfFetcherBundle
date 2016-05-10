<?php

namespace Kasifi\PdfFetcherBundle\Processor;

use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Goutte\Client;

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

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getCronFrequency()
    {
        return '0 19 * * * *';
    }

    /**
     * @param string $url
     *
     * @param array  $headers
     *
     * @return string
     */
    public function downloadDocument($url, $headers = [])
    {
        $guzzleClient = $this->client->getClient();
        $request = new Request('GET', $url);
        $this->logger->info('Downloading document', ['url' => $url]);
        $response = $guzzleClient->send($request, $headers);
        $this->logger->info('Document downloaded', ['url' => $url]);
        return $response->getBody()->getContents();
    }

    public function storeDocument($meta, $content)
    {
        $this->storedDocuments[] = [
            'meta'    => $meta,
            'content' => $content,
        ];
    }

    /**
     * @see http://symfony.com/doc/current/components/browser_kit/introduction.html
     */
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
        dump($this->getDocuments());
        throw new \Exception('Implement dispatcher.');
        // @todo dispatch event
    }

    /**
     * @return array
     */
    private function getDocuments()
    {
        return $this->storedDocuments;
    }
}
