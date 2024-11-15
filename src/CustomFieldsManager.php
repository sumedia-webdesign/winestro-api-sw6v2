<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi;

use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Sumedia\WinestroApi\Winestro\DataMapper\CustomFieldsMapper;

class CustomFieldsManager implements CustomFieldsManagerInterface
{
    public function __construct(
        private RepositoryManagerInterface $repositoryManager,
        private CustomFieldsMapper  $customFieldsMapper
    ){}

    public function createCustomFieldSetByMappingKey(string $mappingKey): string
    {
        $customFieldSet = $this->getCustomFieldSetByMappingKey($mappingKey);
        if (null !== $customFieldSet) {
            return $customFieldSet->getId();
        }

        $customField = $this->customFieldsMapper->mapKey($mappingKey);
        if ($customField === null) {
            throw new \RuntimeException(sprintf('Custom Field "%s" not found', $mappingKey));
        }

        $id = Uuid::randomHex();
        $this->repositoryManager->create('custom_field_set', [[ ... $customField,
            'id' => $id
        ]]);
        return $id;
    }

    public function getCustomFieldSetByMappingKey(string $mappingKey): ?CustomFieldSetEntity
    {
        if (false !== strpos($mappingKey, '.')) {
            throw new \RuntimeException(sprintf('Invalid Custom Field Set mapping key "%s"', $mappingKey));
        }

        $customFieldSetMapping = $this->customFieldsMapper->mapKey($mappingKey);
        if ($customFieldSetMapping === null) {
            throw new \RuntimeException(sprintf('Custom Field Set "%s" not found', $mappingKey));
        }

        return $this->repositoryManager->search(
            'custom_field_set',
            new Criteria([$customFieldSetMapping['id']])
        )->first();
    }

    public function updateCustomFieldSetByMappingKey(string $mappingKey): void
    {
        $customFieldSet = $this->getCustomFieldSetByMappingKey($mappingKey);
        if ($customFieldSet === null) {
            throw new \RuntimeException(sprintf('Custom Field Set with mapping key "%s" could not be loaded', $mappingKey));
        }

        $customFieldSetMapping = $this->customFieldsMapper->mapKey($mappingKey);
        if ($customFieldSetMapping === null) {
            throw new \RuntimeException(sprintf('Custom Field Set Mapping with mapping key "%s" could not be loaded', $mappingKey));
        }

        $this->repositoryManager->update('custom_field_set', [$customFieldSetMapping]);
    }

    public function createCustomFieldByMappingKey(string $mappingKey): string
    {
        if (false === strpos($mappingKey, '.')) {
            throw new \RuntimeException(sprintf('Invalid Custom Field mapping key "%s"', $mappingKey));
        }
        $parts = explode('.', $mappingKey);

        $customFieldMapping = $this->customFieldsMapper->mapKey($mappingKey);
        if ($customFieldMapping === null) {
            throw new \RuntimeException(sprintf('Could not load Custom Field Mapping for mapping key "%s"', $mappingKey));
        }

        $customField = $this->getCustomFieldByMappingKey($mappingKey);
        if ($customField) {
            throw new \RuntimeException(sprintf('Custom Field with mapping key "%s" already exists.', $mappingKey));
        }

        $customFieldSet = $this->getCustomFieldSetByMappingKey($parts[0]);
        if (null === $customFieldSet) {
            throw new \RuntimeException(sprintf('Could not load Custom Field Set by mapping key "%s"', $mappingKey));
        }

        $this->repositoryManager->create('custom_field_set', [$customFieldMapping]);
        return $customFieldMapping['id'];
    }

    public function getCustomFieldByMappingKey(string $mappingKey): ?CustomFieldSetEntity
    {
        if (false === strpos($mappingKey, '.')) {
            throw new \RuntimeException(sprintf('Invalid Custom Field mapping key "%s"', $mappingKey));
        }

        $customFieldMapping = $this->customFieldsMapper->mapKey($mappingKey);
        if ($customFieldMapping === null) {
            throw new \RuntimeException(sprintf('Custom Field "%s" not found', $mappingKey));
        }

        return $this->repositoryManager->search(
            'custom_field_set',
            new Criteria([$customFieldMapping['id']])
        )->first();
    }

    public function updateCustomFieldByMappingKey(string $mappingKey): void
    {
        $customField = $this->getCustomFieldByMappingKey($mappingKey);
        if ($customField === null) {
            throw new \RuntimeException(sprintf('Custom Field with mapping key "%s" could not be loaded', $mappingKey));
        }

        $customFieldMapping = $this->customFieldsMapper->mapKey($mappingKey);
        if ($customFieldMapping === null) {
            throw new \RuntimeException(sprintf('Custom Field Mapping with mapping key "%s" could not be loaded', $mappingKey));
        }

        $this->repositoryManager->update('custom_field', [$customFieldMapping]);
    }
}
