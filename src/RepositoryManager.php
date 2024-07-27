<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Symfony\Component\DependencyInjection\Container;

class RepositoryManager implements RepositoryManagerInterface
{
    public function __construct(
        private Container $container,
        private Context $context
    ){}

    public function search(string $repository, Criteria $criteria, Context $context = null): EntityCollection
    {
        $context = null === $context ? $this->context : $context;
        return $this->getRepository($repository)->search($criteria, $context);
    }

    public function searchIds(string $repository, Criteria $criteria, Context $context = null): IdSearchResult
    {
        $context = null === $context ? $this->context : $context;
        return $this->getRepository($repository)->searchIds($criteria, $context);
    }

    public function create(string $repository, array $data, Context $context = null): void
    {
        $context = null === $context ? $this->context : $context;
        $this->getRepository($repository)->create($data, $context);
    }

    public function upsert(string $repository, array $data, Context $context = null): void
    {
        $context = null === $context ? $this->context : $context;
        $this->getRepository($repository)->upsert($data, $context);
    }

    public function update(string $repository, array $data, Context $context = null): void
    {
        $context = null === $context ? $this->context : $context;
        $this->getRepository($repository)->update($data, $context);
    }

    public function delete(string $repository, array $ids, Context $context = null): void
    {
        $context = null === $context ? $this->context : $context;
        $this->getRepository($repository)->delete($ids, $context);
    }

    public function getRepository(string $repository): EntityRepository
    {
        $repository = $repository . '.repository';
        return $this->container->get($repository);
    }
}