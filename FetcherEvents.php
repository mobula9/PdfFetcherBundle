<?php

namespace Kasifi\PdfFetcherBundle;
/**
 * Contains all events thrown in the Kasifi\PdfFetcherBundle
 */
final class FetcherEvents
{
    /**
     * The CHANGE_PASSWORD_INITIALIZE event occurs when the change password process is initialized.
     *
     * This event allows you to modify the default values of the user before binding the form.
     *
     * @Event("FOS\UserBundle\Event\GetResponseUserEvent")
     */
    const CRAWL_FINISHED = 'kasifi_pdf_fetcher.events.crawl_finished';

}
