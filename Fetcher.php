<?php

namespace Kasifi\PdfFetcherBundle;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Kasifi\PdfFetcherBundle\Processor\AbstractProcessor;
use Kasifi\PdfFetcherBundle\Processor\ProcessorInterface;

class Fetcher
{
    /** @var AbstractProcessor */
    private $processor;

    /** @var ProcessorInterface[] */
    private $availableProcessors = [];

    /**
     * @param AbstractProcessor $processor
     */
    public function addAvailableProcessor(AbstractProcessor $processor)
    {
        $this->availableProcessors[$processor->getId()] = $processor;
    }

    /**
     * @return ProcessorInterface[]
     */
    public function getAvailableProcessors()
    {
        return $this->availableProcessors;
    }

    /**
     * @return ProcessorInterface
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @param string $processorId
     */
    public function selectProcessor($processorId)
    {
        $this->processor = $this->availableProcessors[$processorId];
    }

    /**
     *
     * @return ArrayCollection
     *
     * @throws Exception
     */
    public function fetchDocuments()
    {
        $this->processor->fetchDocuments();
    }
}
