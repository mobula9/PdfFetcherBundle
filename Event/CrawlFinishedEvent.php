<?php

namespace Kasifi\PdfFetcherBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class CrawlFinishedEvent extends Event
{
    private $documents;

    public function __construct(array $documents)
    {
        $this->documents = $documents;
    }

    /**
     * @return array
     */
    public function getDocuments()
    {
        return $this->documents;
    }
}
