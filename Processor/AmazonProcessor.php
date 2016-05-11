<?php

namespace Kasifi\PdfFetcherBundle\Processor;

use Symfony\Component\DomCrawler\Crawler;

class AmazonProcessor extends AbstractProcessor implements ProcessorInterface
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
        return 'amazon';
    }

    public function getName()
    {
        return 'Amazon Document fetcher';
    }

    /**
     * @return Crawler
     */
    public function login()
    {
        $crawler = $this->client->request('GET', 'https://www.amazon.fr/ap/signin?_encoding=UTF8&openid.assoc_handle=frflex&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.mode=checkid_setup&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.ns.pape=http%3A%2F%2Fspecs.openid.net%2Fextensions%2Fpape%2F1.0&openid.pape.max_auth_age=0&openid.return_to=https%3A%2F%2Fwww.amazon.fr%2F%3Fref_%3Dnav_ya_signin');
        $form = $crawler->selectButton('signInSubmit')->form();

        $form['email'] = $this->login;
        $form['password'] = $this->password;
        $this->logger->info('Fill login form', ['login' => $this->login]);

        return $this->client->submit($form);
    }

    /**
     * @param Crawler $successPage
     */
    public function crawl(Crawler $successPage = null)
    {
//        // Crawl to history page
        $this->logger->info('Crawl page', ['name' => 'Last 6 month page']);
        $crawler = $this->client->request('GET', 'https://www.amazon.fr/gp/your-account/order-history?opt=ab&digitalOrders=1&unifiedOrders=1&returnTo=&__mk_fr_FR=%C3%85M%C3%85%C5%BD%C3%95%C3%91&orderFilter=months-6');

//        // Parse list
        $orders = $crawler->filter('.order');
        $this->logger->info('Item(s) found', ['count' => count($orders)]);
        // foreach order
        $orders->each(function (Crawler $order) {

            // add meta

            $meta = ['items' => []];
            $items = $order->filter('.shipment .a-fixed-right-grid-inner .a-fixed-left-grid-col.a-col-right');
            if (count($items)) {

                // foreach items
                $items->each(function (Crawler $item) use (&$meta) {
                    $itemData = [];
                    //dump('=====================ITEM', $item->text());
                    $amount = null;
                    $amountElement = $item->filter('.a-color-price');
                    if ($amountElement) {
                        $itemData['amount'] = trim($amountElement->text());
                    }

                    $label = null;
                    $labelElement = $item->filter('.a-link-normal');
                    if ($labelElement) {
                        $itemData['label'] = trim($labelElement->text());
                    }
                    $meta['items'][] = $itemData;
                });
            }
            //dump($meta);

            // download file
            if (count($link = $order->selectLink('Imprimer une facture'))) {
                $link = $link->link();
                $documentData = $this->downloadDocument($link);
                $meta['type'] = 'full_order';
                $documentData['meta'] = $meta;
                $this->storeDocument($documentData);
            } elseif (count($link = $order->selectLink('Facture (disponible pour certains articles)'))) {
                $link = $link->link();
                $meta['type'] = 'partial_order';
                $documentData = $this->downloadDocument($link);
                $documentData['meta'] = $meta;
                $this->storeDocument($documentData);
            } elseif (count($link = $order->selectLink('Demander une facture'))) {
                $meta['type'] = 'order_to_ask';
            }
        });
    }
}
