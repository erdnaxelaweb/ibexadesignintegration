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
use ErdnaxelaWeb\IbexaDesignIntegration\Document\DocumentSearchResultParser;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerActiveFiltersListBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerSearchFormBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\DocumentSearchAdapter;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Novactive\EzSolrSearchExtra\Query\DocumentQuery;
use Novactive\EzSolrSearchExtra\Repository\DocumentSearchServiceInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractSearchType<DocumentQuery>
 */
class DocumentSearchType extends AbstractSearchType
{
    public function __construct(
        protected DocumentSearchServiceInterface $searchService,
        protected DocumentSearchResultParser $documentSearchResultParser,
        PagerSearchFormBuilder          $pagerSearchFormBuilder,
        PagerActiveFiltersListBuilder   $pagerActiveFiltersListBuilder,
        string                          $searchFormName,
        PagerDefinition                 $pagerDefinition,
        ?Request                         $request,
        SearchData                      $defaultSearchData = new SearchData(),
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

    public function getAdapter(): PagerAdapterInterface
    {
        return new DocumentSearchAdapter(
            $this->query,
            $this->searchService,
            $this->documentSearchResultParser,
            [$this, 'getFiltersForm'],
            [$this, 'getActiveFilters'],
        );
    }

    protected function initializeQuery(): void
    {
        $this->query = new DocumentQuery();
    }
}
