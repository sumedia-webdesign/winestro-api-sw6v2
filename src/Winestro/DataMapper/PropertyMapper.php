<?php declare(strict_types=1);

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\WinestroApi\Winestro\DataMapper;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Sumedia\WinestroApi\RepositoryManagerInterface;

class PropertyMapper implements DataMapperInterface
{
    const COUNTRY = 'country';
    const YEAR = 'year';
    const KIND = 'kind';
    const QUALITY = 'quality';
    const TASTE = 'taste';
    const REGION = 'region';
    const ARTICLE_GROUP = 'articleGroup';
    const INGREDIENTS = 'ingredients';
    const SUGAR = 'sugar';
    const ALCOHOL = 'alcohol';
    const ACID = 'acid';
    const SULFITS = 'sulfits';
    const NUANCES = 'nuances';
    const AWARDS = 'awards';
    const BOTTLES = 'bottles';
    const CATEGORY = 'category';
    const ALLERGENS = 'allergens';
    const CALORIES = 'calories';
    const PROTEINS = 'proteins';
    const AREA = 'area';
    const LOCATION = 'location';
    const DEVELOPMENT = 'development';
    const DRINKING_TEMPERATURE = 'drainingTemperature';
    const FAT = 'fat';
    const UNSATURATED_FAT = 'unsaturatedFat';
    const CARBONHYDRATES = 'carbonhydrates';
    const SALT = 'salt';
    const FIBER = 'fiber';
    const VITAMINS = 'vitamins';

    private array $properties = [
        'country' => [
            'id' => '0190ec4e272a75eaac87b8c17d809d4c',
            'en-GB' => 'Country',
            'de-DE' => 'Land'
        ],
        'year' => [
            'id' => '0190ec4e272c72cd955e25df4cc0c73b',
            'en-GB' => 'Year',
            'de-DE' => 'Jahrgang'
        ],
        'kind' => [
            'id' => '0190ec4e272d7d0db1c747d51df92841',
            'en-GB' => 'Kind',
            'de-DE' => 'Rebsorte'
        ],
        'quality' => [
            'id' => '0190ec4e272f7fc08c99d0916d6da01b',
            'en-GB' => 'Quality',
            'de-DE' => 'Qualität'
        ],
        'taste' => [
            'id' => '0190ec4e273070fbb6b1ac5554d398a1',
            'en-GB' => 'Taste',
            'de-DE' => 'Geschmack'
        ],
        'region' => [
            'id' => '0190ec4e27327d1a9c38fa951e80de7b',
            'en-GB' => 'Region',
            'de-DE' => 'Region'
        ],
        'articleGroup' => [
            'id' => '0190ec4e27327d1a9c38fa9773fccd7c',
            'en-GB' => 'Article Group',
            'de-DE' => 'Artikelgruppe'
        ],
        'ingredients' => [
            'id' => '0190ec4e27337a87869ecf0d1e068b50',
            'en-GB' => 'Ingredients',
            'de-DE' => 'Zutat'
        ],
        'sugar' => [
            'id' => '0190ec4e27357a81a46ac73792704078',
            'en-GB' => 'Sugar',
            'de-DE' => 'Zucker'
        ],
        'alcohol' => [
            'id' => '0190ec4e27557449a6cb3f0452257991',
            'en-GB' => 'Alcohol',
            'de-DE' => 'Alkohol'
        ],
        'acid' => [
            'id' => '0190ec4e27567493a58c16fe5f132c4d',
            'en-GB' => 'Acid',
            'de-DE' => 'Säure'
        ],
        'sulfits' => [
            'id' => '0190ec4e27567493a58c170042cf439b',
            'en-GB' => 'Sulfits',
            'de-DE' => 'Sulfite'
        ],
        'nuances' => [
            'id' => '0190ec4e27567493a58c1702947726ee',
            'en-GB' => 'Nuances',
            'de-DE' => 'Nuancen'
        ],
        'awards' => [
            'id' => '0190ec4e275773a39255118c21cbca4f',
            'en-GB' => 'Awards',
            'de-DE' => 'Auszeichnung'
        ],
        'bottles' => [
            'id' => '0190ec4e27597d6a86b470461a51afdc',
            'en-GB' => 'Bottles included',
            'de-DE' => 'Flaschenanzahl'
        ],
        'category' => [
            'id' => '0190ec4e275b7d59b90af19bf214fbb2',
            'en-GB' => 'Category',
            'de-DE' => 'Kategorie'
        ],
        'allergens' => [
            'id' => '0190ec4e276574e0be1235301bb8b7ed',
            'en-GB' => 'Allergens',
            'de-DE' => 'Allergene'
        ],
        'calories' => [
            'id' => '0190ec4e2769743fb0aa32cdadd4baa8',
            'en-GB' => 'Calories',
            'de-DE' => 'Kalorien'
        ],
        'protein' => [
            'id' => '0190ec4e277a767aa0b1759cf14d2735',
            'en-GB' => 'Protein',
            'de-DE' => 'Eiweiß'
        ],
        'area' => [
            'id' => '0190ec4e277b7765966803e017658155',
            'en-GB' => 'Area',
            'de-DE' => 'Anbaugebiet'
        ],
        'location' => [
            'id' => '0190ec4e277c7dc7bda001a03887aa30',
            'en-GB' => 'Location',
            'de-DE' => 'Lage'
        ],
        'development' => [
            'id' => '0190ec4e27807914b752ce3044b3057c',
            'en-GB' => 'Development',
            'de-DE' => 'Ausbau'
        ],
        'drinkingTemperature' => [
            'id' => '0190ec4e27817b89aa69d84961f48640',
            'en-GB' => 'Drinking Temperature',
            'de-DE' => 'Trinktemperatur'
        ],
        'fat' => [
            'id' => '0190ec4e278a7f3580f8882893283d53',
            'en-GB' => 'Fat',
            'de-DE' => 'Fettsäuren'
        ],
        'unsaturatedFat' => [
            'id' => '0190ec4e278a7f3580f8882a655c49c3',
            'en-GB' => 'Unsaturated fat',
            'de-DE' => 'Ungesättigte Fettsäuren'
        ],
        'carbonhydrates' => [
            'id' => '0190ec4e278b7aa992c0117f4944d7d5',
            'en-GB' => 'Carbonhydrates',
            'de-DE' => 'Kohlenhydrate'
        ],
        'salt' => [
            'id' => '0190ec4e2790717c8370f97f2c6c9392',
            'en-GB' => 'Salt',
            'de-DE' =>'Salz'
        ],
        'fiber' => [
            'id' => '0190ec4e279b7b0ca21081d185f003d6',
            'en-GB' => 'Fiber',
            'de-DE' => 'Ballaststoffe'
        ],
        'vitamins' => [
            'id' => '0190ec4e279f7d78967f6115bd8161a8',
            'en-GB' => 'Vitamins',
            'de-DE' => 'Vitamine'
        ]
    ];

    public function __construct(
        private RepositoryManagerInterface $repositoryManager,
        private Context $context
    ) {
        $isoCode = $this->repositoryManager->search('language',
            (new Criteria([$context->getLanguageId()]))
                ->addAssociation('locale'),
            $context
        )->first()->getLocale()->getCode();

        foreach ($this->getConstants() as $key => $value) {
            $this->map[$key] = [
                'id' => $this->properties[$key]['id'],
                'name' => $this->properties[$key][$isoCode]
            ];
        }
    }

    public function getConstants(): array
    {
        $ref = new \ReflectionClass(self::class);
        return (array) $ref->getConstants();
    }

    public function mapKey(string $key): mixed
    {
        if (isset($this->map[$key])) {
            return $this->map[$key];
        }
        return null;
    }

    public function toArray(): array
    {
        return $this->map;
    }
}