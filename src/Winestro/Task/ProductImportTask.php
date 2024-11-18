<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Sumedia\WinestroApi\Config;
use Sumedia\WinestroApi\RepositoryManager;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;
use Sumedia\WinestroApi\Winestro\RequestManager;
use Sumedia\WinestroApi\Winestro\Task\ProductImport\ProductDataBuilder;
use Sumedia\WinestroApi\Winestro\TaskManager;
use Symfony\Component\DependencyInjection\Container;

class ProductImportTask extends AbstractTask
{
    public function __construct(
        Container $container,
        Config $config,
        TaskManager $taskManager,
        RepositoryManager $repositoryManager,
        RequestManager $requestManager,
        LogManagerInterface $logManager,
        private ProductDataBuilder $productDataBuilder
    ){
        parent::__construct($container, $config, $taskManager, $repositoryManager, $requestManager, $logManager);
    }

    public function execute(TaskInterface $parentTask = null): void
    {
        $this->_execute($parentTask, [$this, 'productImport']);
    }

    public function productImport(): void
    {
        $connection = $this->getWinestroConnection();
        $request = $this->requestManager->createRequest(RequestManager::GET_ARTICLES_FROM_WINESTRO_REQUEST);
        $response = $connection->executeRequest($request);
        $this->setParameter($this['winestroConnectionId'] . '-' . RequestManager::GET_ARTICLES_FROM_WINESTRO_RESPONSE, $response);

        $articles = $response->toArray();
        $this->logManager->logProcess('[task] fetched ' . count($articles) . ' from winestro.cloud');

        $this->productDataBuilder->build($this, $articles);
        $products = $this->productDataBuilder->getProducts();
        $this->logManager->logProcess('[task] got ' . count($products) . ' valid products');

        $productsImportedCount = 0;
        $newProducts = 0;
        $editedProducts = 0;
        $productIds = [];
        foreach ($products as $key => $product) {
            try {
                $this->repositoryManager->upsert('product', [$product]);
                if (is_int($key)) {
                    $newProducts++;
                } else {
                    $editedProducts++;
                }
                $productsImportedCount++;
                $productIds[] = $product['id'];
            } catch(\Exception $e) {
                $this->logManager->logException($e);
            }
        }

        $this->logManager->logProcess('[task] imported ' . $productsImportedCount . ' products, ' . $newProducts . ' new and ' . $editedProducts . ' edited, successfully');

        if ($this['enabled']['activestatus']) {
            $products = $this->repositoryManager->search('product',
                (new Criteria())
                    ->addFilter(new NotFilter('or', [
                        new EqualsFilter('customFields.sumedia_winestro_product_details_article_number', null),
                        new EqualsFilter('customFields.sumedia_winestro_product_details_article_number', ''),
                        new EqualsAnyFilter('id', $productIds),
                        new EqualsFilter('active', false)
                    ]))
            );

            $deactivated = 0;
            /** @var \Shopware\Core\Content\Product\ProductEntity $product */
            foreach ($products as $product) {
                if ($product->getCustomFieldsValue('sumedia_winestro_product_switches_activestatus')) {
                    $this->repositoryManager->upsert('product', [[
                        'id' => $product->getId(),
                        'active' => false,
                    ]]);
                    $this->logManager->debug('deactivate ' . $product->getProductNumber());
                    $deactivated++;
                }
            }
        }

        $this->logManager->logProcess("[task] deactivated $deactivated outdated products");
    }
}