<?php

namespace Sumedia\WinestroAPI\Service\Wbo\Command\SetArticles;

use Sumedia\WinestroAPI\Service\Wbo\Response\GetArticle\Article;

class FilterCollection
{
    protected array $filters = [];

    public function __construct()
    {
        $arguments = func_get_args();
        foreach ($arguments as $filter) {
            $this->filters[] = $filter;
        }
    }

    public function execute(Article $article, array &$productData): void
    {
        foreach ($this->filters as $filter) {
            $filter->execute($article, $productData);
        }
    }
}
