<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;

/**
 * @extends AbstractSearchAdapter<Content>
 */
class ContentSearchAdapter extends AbstractSearchAdapter
{
    /**
     * @param array<string, mixed>|array<int, string>               $languageFilter
     * @return SearchResult<Content>
     */
    protected function executeQuery(SearchService $searchService, Query $query, array $languageFilter): SearchResult
    {
        return $searchService->findContent($query, $languageFilter);
    }
}
