<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\DataMapper;

class WinestroArticleDataMapper implements DataMapperInterface
{
    private array $map = [
        'anzahl' => 'count',
        'artikel_nr' => 'articleNumber',
        'artikel_name' => 'name',
        'artikel_beschreibung' => 'description',
        'artikel_jahrgang' => 'year',
        'artikel_sorte' => 'kind',
        'artikel_qualitaet' => 'quality',
        'artikel_geschmack' => 'taste',
        'artikel_zucker' => 'sugar',
        'artikel_alkohol' => 'alcohol',
        'artikel_saeure' => 'acid',
        'artikel_liter' => 'litre',
        'artikel_gewicht' => 'weight',
        'artikel_sulfite' => 'sulfits',
        'artikel_bild' => 'image1',
        'artikel_bild_big' => 'imageBig1',
        'artikel_bild_2' => 'image2',
        'artikel_bild_big_2' => 'imageBig2',
        'artikel_bild_3' => 'image3',
        'artikel_bild_big_3' => 'imageBig3',
        'artikel_bild_4' => 'image4',
        'artikel_bild_big_4' => 'imageBig4',
        'artikel_nuancen' => 'nuances',
        'artikel_nuancen_items' => 'nuancenItems',
        'artikel_auszeichnungen' => 'awards',
        'artikel_auszeichnungen_items' => 'awardsItems',
        'artikel_versandzahl' => 'bottles',
        'artikel_sort_anzahl' => 'bundle',
        'artikel_preis' => 'price',
        'artikel_shopnotiz' => 'shopDescription',
        'artikel_literpreis' => 'litrePrice',
        'artikel_mwst' => 'tax',
        'artikel_brennwert' => 'calories',
        'artikel_brennwert_joule' => 'joule',
        'artikel_allergene' => 'allergens',
        'artikel_eiweiss' => 'protein',
        'artikel_versandfrei' => 'shippingFree',
        'artikel_keinliterpreis' => 'noLitrePrice',
        'artikel_fuellgewicht' => 'fillingWeight',
        'artikel_kilopreis' => 'kiloPrice',
        'artikel_ausgetrunken' => 'drunken',
        'artikel_apnr' => 'apnr',
        'artikel_lage' => 'location',
        'artikel_expertise' => 'expertise',
        'artikel_typ' => 'articleGroup',
        'artikel_typ_id' => 'articleGroupId',
        'artikel_farbe' => 'color',
        'artikel_trinktemperatur' => 'drinkingTemperature',
        'artikel_lagertemperatur' => 'storingTemperature',
        'artikel_ausbau' => 'development',
        'artikel_lagerfaehigkeit' => 'shelfLife',
        'artikel_boden' => 'grounds',
        'artikel_notiz' => 'productNote',
        'artikel_videolink' => 'videolink',
        'artikel_land' => 'country',
        'artikel_region' => 'region',
        'artikel_anbaugebiet' => 'area',
        'artikel_bestand_warnung_ab' => 'stockWarning',
        'artikel_erzeuger' => 'manufacturerId',
        'artikel_erzeuger_name' => 'manufacturer',
        'artikel_erzeuger_nummer' => 'manufacturerNumber',
        'artikel_erzeuger_text' => 'producerBottlingText',
        'artikel_kategorie' => 'category',
        'artikel_verpackung' => 'unitId',
        'artikel_verpackung_bezeichnung' => 'unit',
        'artikel_verpackung_inhalt' => 'unitQuantity',
        'artikel_ean13' => 'ean',
        'artikel_ean13_kiste' => 'eanBox',
        'artikel_zutaten' => 'ingredients',
        'artikel_mhd' => 'bestBeforeDate',
        'artikel_fett' => 'fat',
        'artikel_fetts' => 'unsaturatedFat',
        'artikel_kohlenhydrate' => 'carbonhydrates',
        'artikel_salz' => 'salt',
        'artikel_ballast' => 'fiber',
        'artikel_vitamine' => 'vitamins',
        'artikel_labeltext' => 'eLabelFreeText',
        'artikel_warengruppen' => 'waregroups'
    ];

    public function mapKey(string $key): string
    {
        if (!isset($this->map[$key])) {
            return $key;
        }
        return $this->map[$key];
    }

    public function toArray(): array
    {
        return $this->map;
    }
}