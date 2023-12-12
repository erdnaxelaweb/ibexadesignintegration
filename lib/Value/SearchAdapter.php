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
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Core\Pagination\Pagerfanta\LocationSearchAdapter;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;

class SearchAdapter extends LocationSearchAdapter
{
    private ?FormBuilderInterface $filtersFormBuilder = null;

    public function __construct(
        Query $query,
        SearchService $searchService,
        protected ContentTransformer $contentTransformer,
        protected $filtersCallback,
        protected $activeFiltersCallback,
        array $languageFilter = []
    ) {
        parent::__construct($query, $searchService, $languageFilter);
    }

    public function getSlice($offset, $length)
    {
        $results = parent::getSlice($offset, $length);
        $list = [];
        foreach ($results as $result) {
            $list[] = [
                'locationId' => $result->id,
                'content' => ($this->contentTransformer)($result->getContent(), $result),
            ];
        }
        return $list;
    }

    protected function getFiltersFormBuilder(): FormBuilderInterface
    {
        if (! $this->filtersFormBuilder) {
            $this->filtersFormBuilder = ($this->filtersCallback)($this->getAggregations());
        }
        return $this->filtersFormBuilder;
    }

    public function getFilters(): FormView
    {
        return $this->getFiltersFormBuilder()
            ->getForm()
            ->createView();
    }

    public function getActiveFilters(): array
    {
        return ($this->activeFiltersCallback)($this->getFiltersFormBuilder());
    }
}
