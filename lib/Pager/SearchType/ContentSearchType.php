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
use ErdnaxelaWeb\IbexaDesignIntegration\Value\ContentSearchAdapter;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends \ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\AbstractSearchType<Query>
 */
class ContentSearchType extends AbstractSearchType
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\SearchService                           $searchService
     * @param \ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer      $contentTransformer
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
        protected SearchService $searchService,
        protected ContentTransformer $contentTransformer,
        EventDispatcherInterface $eventDispatcher,
        PagerSearchFormBuilder $pagerSearchFormBuilder,
        PagerActiveFiltersListBuilder $pagerActiveFiltersListBuilder,
        string $searchFormName,
        PagerDefinition $pagerDefinition,
        ?Request $request,
        SearchData $defaultSearchData = new SearchData(),
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
        return new ContentSearchAdapter(
            $this->query,
            $this->searchService,
            $this->contentTransformer,
            $this->eventDispatcher,
            [$this, 'getFiltersForm'],
            [$this, 'getActiveFilters'],
            $this->context
        );
    }

    protected function initializeQuery(): void
    {
        $this->query = new Query();
    }
}
