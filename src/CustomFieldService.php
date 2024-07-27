<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;
use Shopware\Core\System\CustomField\CustomFieldEntity;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class CustomFieldService
{
    public function __construct(private RepositoryManager $repositoryManager){}

    private function getCustomFieldSetByIdentifier(string $identifier): ?CustomFieldSetEntity
    {
        return $this->repositoryManager->search('custom_field_set',
            (new Criteria())->addFilter(new EqualsFilter('name', $identifier))
        )->first();
    }

    private function createCustomFieldSet(string $identifier, string $nameEN, string $nameDE, string $entityName): void
    {
        $customFieldSet = $this->getCustomFieldSetByIdentifier($identifier);
        if (null === $customFieldSet) {
            $customFieldSetId = Uuid::randomHex();
            $data = [
                'id' => $customFieldSetId,
                'name' => $identifier,
                'config' => [
                    'label' => [
                        'en-GB' => $nameEN,
                        'de-DE' => $nameDE,
                    ],
                ],
                'relations' => [
                    ['entityName' => $entityName],
                ]
            ];
            $this->repositoryManager->create('custom_field_set', $data);
        }
    }

    private function getCustomFieldByIdentifier(string $identifier): ?CustomFieldEntity
    {
        return $this->repositoryManager->search('custom_field',
            (new Criteria())->addFilter(new EqualsFilter('name', $identifier))
        )->first();
    }

    private function createCustomField(
        string $customFieldSetId,
        string $identifier,
        string $nameEN,
        string $nameDE,
        string $type,
        int $position,
    ): void {

        $customField = $this->getCustomFieldByIdentifier($identifier);
        if (null === $customField) {

            $config['label'] = [
                'en-GB' => $nameEN,
                'de-DE' => $nameDE,
            ];
            $config['customFieldPosition'] = $position;
            if ($type === CustomFieldTypes::HTML) {
                $config['componentName'] = 'sw-text-editor';
                $config['customFieldType'] = CustomFieldTypes::HTML;
            }

            $customFieldId = Uuid::randomHex();
            $data = [
                'customFieldSetId' => $customFieldSetId,
                'id' => $customFieldId,
                'name' => $identifier,
                'type' => $type,
                'config' => $config
            ];

            $this->repositoryManager->create('custom_field', $data);
        }
    }
}