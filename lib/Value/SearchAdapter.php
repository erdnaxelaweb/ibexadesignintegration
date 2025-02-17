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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Core\Pagination\Pagerfanta\AbstractSearchResultAdapter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class SearchAdapter extends AbstractSearchResultAdapter implements PagerAdapterInterface
{
    public const SEARCH_TYPE_LOCATION = 'location';

    public const SEARCH_TYPE_CONTENT = 'content';

    private ?FormInterface $filtersFormBuilder = null;

    public function __construct(
        Query $query,
        SearchService $searchService,
        protected string $searchType,
        protected ContentTransformer $contentTransformer,
        protected $filtersCallback,
        protected $activeFiltersCallback,
        array $languageFilter = []
    ) {
        parent::__construct($query, $searchService, $languageFilter);
    }

    public function getSlice($offset, $length)
    {
        $searchHits = parent::getSlice($offset, $length);
        $list = [];
        foreach ($searchHits as $searchHit) {
            $result = $searchHit->valueObject;
            if ($result instanceof \Ibexa\Core\Repository\Values\Content\Location) {
                $list[] = ($this->contentTransformer)($result->getContent(), $result);
            }
            if ($result instanceof \Ibexa\Core\Repository\Values\Content\Content) {
                $list[] = ($this->contentTransformer)($result);
            }
        }
        return $list;
    }

    protected function getFiltersFormBuilder(): FormInterface
    {
        if (! $this->filtersFormBuilder) {
            $this->filtersFormBuilder = ($this->filtersCallback)($this->getAggregations());
        }
        return $this->filtersFormBuilder;
    }

    public function getFilters(): FormView
    {
        return $this->getFiltersFormBuilder()
            ->createView();
    }

    public function getActiveFilters(): array
    {
        return ($this->activeFiltersCallback)($this->getFiltersFormBuilder());
    }

    protected function executeQuery(SearchService $searchService, Query $query, array $languageFilter): SearchResult
    {
        if ($this->searchType === self::SEARCH_TYPE_CONTENT) {
            return $searchService->findContent($query, $languageFilter);
        }
        return $searchService->findLocations($query, $languageFilter);
    }
}
