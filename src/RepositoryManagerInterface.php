<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;

interface RepositoryManagerInterface
{
    public function search(string $repository, Criteria $criteria, Context $context = null): EntityCollection;
    public function searchIds(string $repository, Criteria $criteria, Context $context = null): IdSearchResult;
    public function create(string $repository, array $data, Context $context = null): void;
    public function upsert(string $repository, array $data, Context $context = null): void;
    public function update(string $repository, array $data, Context $context = null): void;
    public function delete(string $repository, array $ids, Context $context = null): void;
    public function getRepository(string $repository): EntityRepository;
}