<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerActiveFiltersListBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerSearchFormBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\LocationSearchAdapter;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends \ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\AbstractSearchType<LocationQuery>
 */
class LocationSearchType extends AbstractSearchType
{
    public function __construct(
        protected SearchService $searchService,
        protected ContentTransformer $contentTransformer,
        PagerSearchFormBuilder $pagerSearchFormBuilder,
        PagerActiveFiltersListBuilder $pagerActiveFiltersListBuilder,
        string $searchFormName,
        PagerDefinition $pagerDefinition,
        ?Request $request,
        SearchData $defaultSearchData = new SearchData()
    ) {
        parent::__construct(
            $pagerSearchFormBuilder,
            $pagerActiveFiltersListBuilder,
            $searchFormName,
            $pagerDefinition,
            $request,
            $defaultSearchData
        );
    }

    public function initializeQuery(): void
    {
        $this->query = new LocationQuery();
    }

    public function getAdapter(): PagerAdapterInterface
    {
        return new LocationSearchAdapter(
            $this->query,
            $this->searchService,
            $this->contentTransformer,
            [$this, 'getFiltersForm'],
            [$this, 'getActiveFilters']
        );
    }
}
