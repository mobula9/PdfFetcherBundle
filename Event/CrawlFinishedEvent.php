<?php

namespace Kasifi\PdfFetcherBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class CrawlFinishedEvent extends Event
{
    /** @var string */
    private $processorId;

    /** @var array */
    private $documents;

    /**
     * CrawlFinishedEvent constructor.
     *
     * @param string $processorId
     * @param array  $documents
     */
    public function __construct($processorId, array $documents)
    {
        $this->processorId = $processorId;
        $this->documents = $documents;
    }

    /**
     * @return array
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @return string
     */
    public function getProcessorId()
    {
        return $this->processorId;
    }
}
