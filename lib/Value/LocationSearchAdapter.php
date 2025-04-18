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
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;

class LocationSearchAdapter extends AbstractSearchAdapter
{
    /**
     * @param array<string, mixed>|array<int, string>               $languageFilter
     */
    protected function executeQuery(
        SearchService $searchService,
        LocationQuery $query,
        array $languageFilter
    ): SearchResult {
        return $searchService->findLocations($query, $languageFilter);
    }
}
