<?php

namespace Kasifi\PdfFetcherBundle\Processor;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Interface ProcessorInterface.
 */
interface ProcessorInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return Crawler|null
     */
    public function login();

    /**
     * @param Crawler $successPage
     */
    public function crawl(Crawler $successPage = null);
}
