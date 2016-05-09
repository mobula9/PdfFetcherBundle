<?php

namespace Kasifi\PdfFetcherBundle;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Kasifi\PdfFetcherBundle\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;

class Fetcher {
    /**
     * @var LoggerInterface
     */
    private $logger;

    /** @var ProcessorInterface */
    private $processor;

    /** @var ProcessorInterface[] */
    private $availableProcessors = [];

    /**
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ProcessorInterface $processor
     */
    public function addAvailableProcessor(ProcessorInterface $processor)
    {
        $this->availableProcessors[$processor->getConfiguration()['id']] = $processor;
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
     * @param ProcessorInterface $processor
     */
    public function setProcessor(ProcessorInterface $processor)
    {
        $this->processor = $processor;
    }

    /**
     *
     * @return ArrayCollection
     *
     * @throws Exception
     */
    public function fetch()
    {
        // TODO
    }
}
