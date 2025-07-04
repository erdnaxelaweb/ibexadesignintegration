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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractSearchType<DocumentQuery>
 */
class DocumentSearchType extends AbstractSearchType
{
    /**
     * @param \Novactive\EzSolrSearchExtra\Repository\DocumentSearchServiceInterface   $searchService
     * @param \ErdnaxelaWeb\IbexaDesignIntegration\Document\DocumentSearchResultParser $documentSearchResultParser
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface              $eventDispatcher
     * @param \ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerSearchFormBuilder        $pagerSearchFormBuilder
     * @param \ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerActiveFiltersListBuilder $pagerActiveFiltersListBuilder
     * @param string                                                                   $searchFormName
     * @param \ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition          $pagerDefinition
     * @param \Symfony\Component\HttpFoundation\Request|null                           $request
     * @param \ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData                    $defaultSearchData
     * @param array<string, mixed>                                                                    $context
     */
    public function __construct(
        protected DocumentSearchServiceInterface $searchService,
        protected DocumentSearchResultParser $documentSearchResultParser,
        EventDispatcherInterface $eventDispatcher,
        PagerSearchFormBuilder          $pagerSearchFormBuilder,
        PagerActiveFiltersListBuilder   $pagerActiveFiltersListBuilder,
        string                          $searchFormName,
        PagerDefinition                 $pagerDefinition,
        ?Request                         $request,
        SearchData                      $defaultSearchData = new SearchData(),
        array $context = []
    ) {
        parent::__construct(
            $pagerSearchFormBuilder,
            $pagerActiveFiltersListBuilder,
            $eventDispatcher,
            $searchFormName,
            $pagerDefinition,
            $request,
            $defaultSearchData,
            $context
        );
    }

    public function getAdapter(): PagerAdapterInterface
    {
        return new DocumentSearchAdapter(
            $this->query,
            $this->searchService,
            $this->documentSearchResultParser,
            $this->eventDispatcher,
            [$this, 'getFiltersForm'],
            [$this, 'getActiveFilters'],
            $this->context
        );
    }

    protected function initializeQuery(): void
    {
        $this->query = new DocumentQuery();
    }
}
