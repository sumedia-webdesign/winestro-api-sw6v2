<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
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
        $products = $this->repositoryManager->search('product',
            (new Criteria())
                ->addFilter(new NotFilter('or', [
                    new EqualsFilter('customFields.sumedia_winestro_product_details_article_number', null),
                    new EqualsFilter('customFields.sumedia_winestro_product_details_article_number', '')
                ]))
        );

        /** @var \Shopware\Core\Content\Product\ProductEntity $product */
        foreach ($products as $product) {
            $articleNumber = $product->getCustomFieldsValue('sumedia_winestro_product_details_article_number');
            if (null === $articleNumber) {
                continue;
            }

            $article = $this->getArticleByArticleNumber($articles, $articleNumber);
            if (null === $article) {
                continue;
            }

            $categoryIds = $this->getCategoryIdsByWaregroups($article['waregroups']);

            foreach ($product->getCategories() as $productCategory) {
                if (!in_array($productCategory->getId(), $categoryIds)) {
                    $categoryIds[] = $productCategory->getId();
                }
            }

            $categoryIds = array_map(function($item) { return ['id' => $item]; }, $categoryIds);

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

    private function getCategoryIdsByWaregroups(array $waregroups): array
    {
        $categories = $this->repositoryManager->search('category', (new Criteria()));
        $categoryIds = [];
        foreach ($waregroups as $categoryString) {
            if (!str_contains($categoryString, '>')) {
                continue;
            }

            $parts = array_map(function($item) { return trim($item); }, explode('>', $categoryString));
            $identifier = $parts[0];
            if ($identifier !== $this['categoryIdentifier']) {
                continue;
            }

            foreach ($categories as $category) {
                $matchString = implode(' > ', array_values($category->getBreadcrumb()));
                if ($matchString === implode(' > ', array_slice($parts, 1))) {
                    if (!in_array($category->getId(), $categoryIds)) {
                        $categoryIds[] = $category->getId();
                    }
                }
            }
        }
        return $categoryIds;
    }
}