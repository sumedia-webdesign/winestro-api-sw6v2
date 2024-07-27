<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Response;

use Sumedia\WinestroApi\Winestro\DataMapper\WinestroArticleDataMapper;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;

class GetCustomersFromWinestroResponse extends AbstractResponse
{
    public function populate(array $data): void
    {
        if (!isset($data['item'])) {
            throw new NoEntriesException('no data for this item');
        }
        $items = isset($data['items']) ? $data['items'] : [$data['item']];
        foreach ($items as $item) {
            $this->data[] = [
                'addressId' => $item['adr_id'],
                'addressNumber' => $item['adr_nr'],
                'firstname' => $item['adr_vorname'],
                'lastname' => $item['adr_nachname'],
                'company' => $item['adr_firma'],
                'zipcode' => $item['adr_plz'],
                'city' => $item['adr_ort'],
                'www' => $item['adr_www'],
                'email' => $item['adr_email'],
                'street' => $item['adr_str'],
                'streetNumber' => $item['adr_str_nr'],
                'country' => $item['adr_land'],
                'phone' => $item['adr_festnetz'],
                'mobile' => $item['adr_mobil'],
                'facsimile' => $item['adr_fax'],
                'note1' => $item['adr_note1'],
                'note2' => $item['adr_note2'],
                'note3' => $item['adr_note3'],
                'note4' => $item['adr_note4'],
                'discount' => $item['adr_rabatt'],
                'priceCategory' => $item['adr_id_preiskategorie'],
                'isNewsletterActive' => $item['adr_newsletter_aktiv'],
                //'paymentType' => $item['adr_zahlungsart'],
                'tax' => $item['adr_kunden_mwst'],
                'salutation' => $item['adr_anrede'],
                'salutationType' => $item['adr_anredenart']
            ];
        }
    }
}
