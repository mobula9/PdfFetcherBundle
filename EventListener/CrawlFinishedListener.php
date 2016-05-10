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
        if (!$this->fs->exists($this->basePath)) {
            $this->fs->mkdir($this->basePath);
        }
        $documents = $event->getDocuments();
        foreach ($documents as $document) {
            $content = $document['content'];
            $filename = Transliterator::urlize(implode(' - ', $document['meta']));
            $filePath = $this->basePath . '/' . $filename;
            $this->fs->dumpFile($filePath, $content);
            $file = new File($filePath);
            $ext = $file->guessExtension();
            if ($ext) {
                $this->fs->rename($filePath, $filePath . '.' . $ext);
            }
        }
    }
}
