<?php

namespace Kasifi\PdfFetcherBundle;
/**
 * Contains all events thrown in the Kasifi\PdfFetcherBundle
 */
final class FetcherEvents
{
    /**
     * @Event("Kasifi\PdfFetcherBundle\Event\CrawlFinishedEvent")
     */
    const CRAWL_FINISHED = 'kasifi_pdf_fetcher.events.crawl_finished';

}
