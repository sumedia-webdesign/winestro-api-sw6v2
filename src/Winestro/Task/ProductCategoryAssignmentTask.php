<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Sumedia\WinestroApi\Winestro\RequestManager;

class ProductCategoryAssignmentTask extends AbstractTask
{
    public function execute(TaskInterface $parentTask = null): void
    {
        $this->_execute($parentTask, [$this, 'productCategoryAssignment']);
    }

    public function productCategoryAssignment(): void
    {
        $connection = $this->getWinestroConnection();
        $request = $this->requestManager->createRequest(RequestManager::GET_ARTICLES_FROM_WINESTRO_REQUEST);
        $response = $connection->executeRequest($request);

        $articles = $response->toArray();

        /** @var \Shopware\Core\Content\Product\ProductEntity $product */
        foreach ($articles as $article) {
            $product = $this->repositoryManager->search('product',
                (new Criteria())
                    ->addAssociation('categories')
                    ->addFilter(new EqualsFilter('customFields.sumedia_winestro_product_details_article_number', $article['articleNumber']))
            )->first();
            if (null === $product) {
                continue;
            }

            $categoryIds = $this->repositoryManager->search('category',
                (new Criteria())->addFilter(new EqualsAnyFilter(
                    'customFields.sumedia_winestro_category_details_category_identifier',
                    $article['waregroups']
                ))
            )->getIds();

            foreach ($product->getCategories() as $productCategory) {
                if (!in_array($productCategory->getId(), $categoryIds)) {
                    $categoryIds[] = $productCategory->getId();
                }
            }

            $categoryIds = array_map(function($item) { return ['id' => $item]; }, (array) $categoryIds);

            $this->repositoryManager->update('product', [[
                'id' => $product->getId(),
                'categories' => [... $categoryIds]
            ]]);
        }
    }

    private function getArticleByArticleNumber(array $articles, string $articleNumber): ?array
    {
        foreach ($articles as $article) {
            if ($article['articleNumber'] === $articleNumber) {
                return $article;
            }
        }
        return null;
    }
}