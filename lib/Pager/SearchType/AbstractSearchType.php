<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

declare(strict_types=1);

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerActiveFiltersListBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerSearchFormBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractSearchType implements SearchTypeInterface
{
    protected Query $query;

    protected SearchData $searchData;

    public function __construct(
        protected PagerSearchFormBuilder        $pagerSearchFormBuilder,
        protected PagerActiveFiltersListBuilder $pagerActiveFiltersListBuilder,
        protected string $searchFormName,
        protected array $configuration,
        protected Request $request,
        protected SearchData $defaultSearchData = new SearchData()
    ) {
        $this->initializeQuery();
        $rawSearchData = $request->get($searchFormName, null);
        $this->searchData = $rawSearchData !== null ? SearchData::createFromRequest(
            $rawSearchData
        ) : $this->defaultSearchData;
    }

    public function getFiltersForm(AggregationResultCollection $aggregationResultCollection): FormInterface
    {
        $formBuilder = $this->pagerSearchFormBuilder->build(
            $this->searchFormName,
            $this->configuration,
            $aggregationResultCollection,
            $this->defaultSearchData
        );

        $form = $formBuilder->getForm();
        $form->handleRequest($this->request);
        return $form;
    }

    public function getActiveFilters(FormInterface $filtersFormBuilder): array
    {
        return $this->pagerActiveFiltersListBuilder->buildList(
            $this->searchFormName,
            $this->configuration,
            $filtersFormBuilder,
            $this->searchData
        );
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    public function getSearchData(): SearchData
    {
        return $this->searchData;
    }

    abstract protected function initializeQuery(): void;

    abstract public function getAdapter(): PagerAdapterInterface;
}
