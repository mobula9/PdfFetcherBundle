<?php

namespace Kasifi\PdfFetcherBundle\EventListener;

use Behat\Transliterator\Transliterator;
use Kasifi\PdfFetcherBundle\Event\CrawlFinishedEvent;
use Kasifi\PdfFetcherBundle\FetcherEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class CrawlFinishedListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var Filesystem
     */
    private $fs;

    public function __construct(Filesystem $filesystem, $basePath)
    {
        $this->basePath = $basePath;
        $this->fs = $filesystem;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FetcherEvents::CRAWL_FINISHED => 'onCrawlFinished',
        ];
    }

    public function onCrawlFinished(CrawlFinishedEvent $event)
    {
        $processorId = $event->getProcessorId();
        $directoryPath = $this->basePath . '/' . $processorId;
        if (!$this->fs->exists($directoryPath)) {
            $this->fs->mkdir($directoryPath);
        }
        $documents = $event->getDocuments();
        foreach ($documents as $document) {
            if (!in_array($document['status'], [200, 302])) {
                throw new \Exception('Document ' . $document['url'] . ' download in error ' . $document['status']);
            }

            $content = $document['content'];
            $filename = $document['filename'];
            $realFilename = (bool)$filename;
            if (!$filename) {
                $filename = Transliterator::urlize(implode(' - ', $document['meta']));
            }

            $filePath = $directoryPath . '/' . $filename;
            $this->fs->dumpFile($filePath, $content);

            if (!$realFilename) {
                $file = new File($filePath);
                $ext = $file->guessExtension();
                if ($ext) {
                    $this->fs->rename($filePath, $filePath . '.' . $ext, true);
                }
            }
        }
    }
}
