<?php

namespace Kasifi\PdfFetcherBundle\Processor;

use Symfony\Component\DomCrawler\Crawler;

class CipavProcessor extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    public function __construct($login, $password)
    {

        $this->login = $login;
        $this->password = $password;
    }

    public function getId()
    {
        return 'cipav';
    }

    public function getName()
    {
        return 'CIPAV Document fetcher';
    }

    /**
     * @return Crawler
     */
    public function login()
    {
        $crawler = $this->client->request('GET', 'https://portail.cipav-retraite.fr/moncompte/login.xhtml');
        $form = $crawler->selectButton('Se connecter')->form();
        $form['jusername'] = $this->login;
        $form['jpassword'] = $this->password;
        $this->logger->info('Fill login form', ['login' => $this->login]);

        return $this->client->submit($form);
    }

    /**
     * @param Crawler $successPage
     */
    public function crawl(Crawler $successPage = null)
    {
        // Go to document list
        $link = $successPage->selectLink('Mes Documents')->link();
        $crawler = $this->client->click($link);

        // Parse table
        $crawler->filter('.TableStandard tr')->each(function (Crawler $node, $i) {
            if ($i > 0) {

                // download file
                $link = $node->selectLink('Télécharger')->link();
                $documentData = $this->downloadDocument($link);
                $documentData['filename'] = null; // because many file have the same name

                // add meta
                $date = $node->filter('td')->first()->text();
                $name = $node->filter('td')->eq(1)->text();
                $documentData['meta'] = ['date' => $date, 'name' => $name];

                $this->storeDocument($documentData);
            }
        });
    }
}
