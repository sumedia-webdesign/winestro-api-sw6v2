<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Sumedia\WinestroApi\Config;
use Sumedia\WinestroApi\RepositoryManager;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;
use Sumedia\WinestroApi\Winestro\RequestManager;
use Sumedia\WinestroApi\Winestro\Task\ProductImageUpdate\ImageUpdater;
use Sumedia\WinestroApi\Winestro\TaskManager;
use Symfony\Component\DependencyInjection\Container;

class ProductImageUpdateTask extends AbstractTask
{
    public function __construct(
        Container $container,
        Config $config,
        TaskManager $taskManager,
        RepositoryManager $repositoryManager,
        RequestManager $requestManager,
        LogManagerInterface $logManager,
        private ImageUpdater $imageUpdater
    ) {
        parent::__construct($container, $config, $taskManager, $repositoryManager, $requestManager, $logManager);
    }

    public function execute(TaskInterface $parentTask = null): void
    {
        $this->_execute($parentTask, [$this, 'productImageUpdate']);
    }

    public function productImageUpdate(): void
    {
        if ($this->parentTask !== null &&
            $this->parentTask->hasParameter($this['winestroConnectionId'] . '-' . RequestManager::GET_ARTICLES_FROM_WINESTRO_RESPONSE)
        ) {
            $response = $this->parentTask->getParameter($this['winestroConnectionId'] . '-' . RequestManager::GET_ARTICLES_FROM_WINESTRO_RESPONSE);
        } else {
            $connection = $this->getWinestroConnection();
            $request = $this->requestManager->createRequest(RequestManager::GET_ARTICLES_FROM_WINESTRO_REQUEST);
            $response = $connection->executeRequest($request);
            $this->setParameter($this['winestroConnectionId'] . '-' . RequestManager::GET_ARTICLES_FROM_WINESTRO_RESPONSE, $response);
        }

        $articles = $response->toArray();
        $products = new ProductCollection($this->repositoryManager->search('product',
            (new Criteria())
                ->addAssociation('media')
                ->addFilter(new NotFilter('or', [
                    new EqualsFilter('customFields.sumedia_winestro_product_details_article_number', null),
                    new EqualsFilter('customFields.sumedia_winestro_product_details_article_number', '')
                ]))
        )->getElements());

        $this->imageUpdater->updateImages($this, $products, $articles);
    }
}