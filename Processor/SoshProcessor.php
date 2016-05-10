<?php

namespace Kasifi\PdfFetcherBundle\Processor;

use Symfony\Component\DomCrawler\Crawler;

class SoshProcessor extends AbstractProcessor implements ProcessorInterface
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
        return 'sosh';
    }

    public function getName()
    {
        return 'Sosh Document fetcher';
    }

    /**
     * @return Crawler
     */
    public function login()
    {
        $crawler = $this->client->request('GET', 'https://id.orange.fr/auth_user/bin/auth_user.cgi?return_url=https://espaceclientv3.orange.fr/');
        $form = $crawler->selectButton('s’identifier')->form();
        $form['credential'] = $this->login;
        $form['password'] = $this->password;
        $this->logger->info('Fill login form', ['login' => $this->login]);

        return $this->client->submit($form);
    }

    /**
     * @param Crawler $successPage
     */
    public function crawl(Crawler $successPage = null)
    {
        // Crawl to history page
        $this->logger->info('Crawl page', ['name' => 'Historique']);
        $crawler = $this->client->request('GET', 'https://espaceclientv3.orange.fr/?page=factures-historique');

        // Parse list
        $items = $crawler->filter('.factures li');
        $this->logger->info('Item(s) found', ['count' => count($items)]);
        $items->each(function (Crawler $node) {
            // download file
            $link = $node->selectLink('ma facture du')->link();
            $documentData = $this->downloadDocument($link);

            // add meta
            $date = $node->filter('.colonneDate')->text();
            $amount = $node->filter('.colonneMontant')->text();
            $documentData['meta'] = ['date' => $date, 'amount' => $amount];

            $this->storeDocument($documentData);
        });
    }
}
