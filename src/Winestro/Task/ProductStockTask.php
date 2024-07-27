<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Sumedia\WinestroApi\Winestro\RequestManager;
use Sumedia\WinestroApi\Winestro\Response\NoEntriesException;

class ProductStockTask extends AbstractTask
{
    public function execute(TaskInterface $parentTask = null): void
    {
        $this->_execute($parentTask, [$this, 'productStock']);
    }

    public function productStock(): void
    {
        $products = $this->repositoryManager->search('product',
            (new Criteria())
                ->addAssociation('media')
                ->addFilter(new NotFilter('or', [
                    new EqualsFilter('customFields.sumedia_winestro_product_details_article_number', null),
                    new EqualsFilter('customFields.sumedia_winestro_product_details_article_number', '')
                ]))
        );

        /** @var \Shopware\Core\Content\Product\ProductEntity $product */
        foreach ($products as $product) {
            $articleNumber = $product->getCustomFieldsValue('sumedia_winestro_product_details_article_number');
            $stockDate = $product->getCustomFieldsValue('sumedia_winestro_product_details_stock_update_date');
            if (!$articleNumber) {
                continue;
            }

            $stock = $product->getStock();

            $now = new \DateTime();
            if (null !== $stockDate) {
                $stockDateTime = (new \DateTime($stockDate))->sub(\DateInterval::createFromDateString('12 hours'));
            } else {
                $stockDateTime = (new \DateTime())->sub(\DateInterval::createFromDateString('12 hours'));
            }
            if (null === $stockDate || $stockDateTime > $now) {
                $connection = $this->getWinestroConnection();
                $request = $this->requestManager->createRequest(RequestManager::GET_STOCK_FROM_WINESTRO_REQUEST);
                $request->setParameter('artikelnr', $articleNumber);
                try {
                    $response = $connection->executeRequest($request);
                    $stock = (int) current($response->toArray());

                    foreach ($this->getExtensions() as $extension) {

                        if ($extension['type'] === 'productStockAdder') {
                            $this->setParameter('stock', $stock);
                            $this->setParameter('articleNumber', $articleNumber);

                            $extension->execute($this);
                            $stock = $this->getParameter('stock');

                            $this->removeParameter('stock');
                            $this->removeParameter('articleNumber');
                        }
                    }

                    $this->repositoryManager->upsert('product', [[
                        'id' => $product->getId(),
                        'stock' => $stock,
                        'customFields' => [
                            'sumedia_winestro_product_details_stock_update_date' => date('Y-m-d H:i:s')
                        ]
                    ]]);
                } catch (NoEntriesException $e) {}
            }
        }
    }
}