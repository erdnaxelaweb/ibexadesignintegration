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
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template Q of Query
 */
abstract class AbstractSearchType implements SearchTypeInterface
{
    /**
     * @var Q
     */
    protected Query $query;

    protected SearchData $searchData;

    /**
     * @param \ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerSearchFormBuilder        $pagerSearchFormBuilder
     * @param \ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerActiveFiltersListBuilder $pagerActiveFiltersListBuilder
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface              $eventDispatcher
     * @param string                                                                   $searchFormName
     * @param \ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition          $pagerDefinition
     * @param \Symfony\Component\HttpFoundation\Request|null                           $request
     * @param \ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData                    $defaultSearchData
     * @param array<string, mixed>                                                                    $context
     */
    public function __construct(
        protected PagerSearchFormBuilder $pagerSearchFormBuilder,
        protected PagerActiveFiltersListBuilder $pagerActiveFiltersListBuilder,
        protected EventDispatcherInterface $eventDispatcher,
        protected string $searchFormName,
        protected PagerDefinition $pagerDefinition,
        protected ?Request $request,
        protected SearchData $defaultSearchData = new SearchData(),
        protected array $context = []
    ) {
        $this->initializeQuery();
        $rawSearchData = $request?->get($searchFormName, null);
        $this->searchData = $rawSearchData !== null ? SearchData::createFromRequest(
            $rawSearchData
        ) : $this->defaultSearchData;
    }

    public function getFiltersForm(AggregationResultCollection $aggregationResultCollection): FormInterface
    {
        $formBuilder = $this->pagerSearchFormBuilder->build(
            $this->searchFormName,
            $this->pagerDefinition,
            $aggregationResultCollection,
            $this->defaultSearchData
        );

        $form = $formBuilder->getForm();
        $form->handleRequest($this->request);
        return $form;
    }

    /**
     * @return ItemInterface[]
     */
    public function getActiveFilters(FormInterface $filtersFormBuilder): array
    {
        return $this->pagerActiveFiltersListBuilder->buildList(
            $this->searchFormName,
            $this->pagerDefinition,
            $filtersFormBuilder,
            $this->searchData
        );
    }

    /**
     * @return Q
     */
    public function getQuery(): Query
    {
        return $this->query;
    }

    public function getSearchData(): SearchData
    {
        return $this->searchData;
    }

    abstract public function getAdapter(): PagerAdapterInterface;

    abstract protected function initializeQuery(): void;
}
