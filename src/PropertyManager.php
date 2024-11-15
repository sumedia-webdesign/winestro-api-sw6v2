<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Sumedia\WinestroApi\Winestro\DataMapper\PropertyMapper;

class PropertyManager implements PropertyManagerInterface
{
    private array $propertyGroupsById = [];
    private array $propertyGroupOptionsByValue = [];

    public function __construct(
        private RepositoryManagerInterface $repositoryManager,
        private PropertyMapper $propertyMapper
    ) {}

    public function getPropertyGroupByMappingKey(string $mappingKey): ?PropertyGroupEntity
    {
        $mapping = $this->propertyMapper->mapKey($mappingKey);
        if ($mapping === null) {
            throw new \RuntimeException(sprintf('Property group mapping for key "%s" not found', $mappingKey));
        }

        return $this->getPropertyGroupById($mapping['id']);
    }

    private function getPropertyGroupById(string $id): ?PropertyGroupEntity
    {
        if (!isset($this->propertyGroupsById[$id])) {
            $this->propertyGroupsById[$id] =
                $this->repositoryManager->search('property_group', new Criteria([$id]))->first();
        }
        return $this->propertyGroupsById[$id];
    }

    public function getOrCreatePropertyGroupOptionByMappingKey(string $mappingKey, mixed $value): string
    {
        $propertyGroup = $this->getPropertyGroupByMappingKey($mappingKey);
        if (!$propertyGroup) {
            throw new \RuntimeException(sprintf('Property group could not be loaded for key "%s" not found', $mappingKey));
        }

        $propertyGroupOption = $this->getPropertyGroupOptionByMappingKey($mappingKey, $value);
        if ($propertyGroupOption) {
            return $propertyGroupOption->getId();
        }

        $id = Uuid::randomHex();
        $this->repositoryManager->create('property_group_option', [[
            'id' => $id,
            'property_group_id' => $propertyGroup->getId(),
            'name' => $value,
        ]]);
        return $id;
    }

    public function getPropertyGroupOptionByMappingKey(string $mappingKey, mixed $value): ?PropertyGroupOptionEntity
    {
        $propertyGroup = $this->getPropertyGroupByMappingKey($mappingKey);
        if (!$propertyGroup) {
            throw new \RuntimeException(sprintf('Property group could not be loaded for key "%s" not found', $mappingKey));
        }

        if (!isset($this->propertyGroupOptionsByValue[md5($propertyGroup->getId() . ':' . $value)])) {
            $this->propertyGroupOptionsByValue[md5($propertyGroup->getId() . ':' . $value)] =
                $this->repositoryManager->search('property_group_option',
                    (new Criteria())
                        ->addFilter(new EqualsFilter('property_group_id', $propertyGroup->getId()))
                        ->addFilter(new EqualsFilter('name', $value))
                )->first();
        }
        return $this->propertyGroupOptionsByValue[md5($propertyGroup->getId() . ':' . $value)];
    }
}
