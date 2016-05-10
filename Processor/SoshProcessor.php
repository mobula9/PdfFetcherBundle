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
        // Go to history
        $crawler = $this->client->request('GET', 'https://espaceclientv3.orange.fr/?page=factures-historique');

        // Parse list
        $crawler->filter('.factures li')->each(function (Crawler $node, $i) {
            // download file
            $link = $node->selectLink('ma facture du')->link(); // .colonneTelecharger a
            $documentData = $this->downloadDocument($link);

            // add meta
            $date = $node->filter('.colonneDate')->text();
            $amount = $node->filter('.colonneMontant')->text();
            $documentData['meta'] = ['date' => $date, 'amount' => $amount];

            $this->storeDocument($documentData);
        });
    }
}
