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

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Ibexa\Core\Pagination\Pagerfanta\AbstractSearchResultAdapter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

abstract class AbstractSearchAdapter extends AbstractSearchResultAdapter implements PagerAdapterInterface
{
    private ?FormInterface $filtersFormBuilder = null;

    /**
     * @param callable(AggregationResultCollection): FormInterface                $filtersCallback
     * @param callable(FormInterface): \Knp\Menu\ItemInterface[]                  $activeFiltersCallback
     * @param array<string, mixed>|array<int, string>                             $languageFilter
     */
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

    /**
     * @param int $offset
     * @param int $length
     *
     * @return Content[]
     * @phpstan-ignore-next-line
     */
    public function getSlice($offset, $length): array
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

    public function getFilters(): FormView
    {
        return $this->getFiltersFormBuilder()
            ->createView();
    }

    public function getActiveFilters(): array
    {
        return call_user_func($this->activeFiltersCallback, $this->getFiltersFormBuilder());
    }

    protected function getFiltersFormBuilder(): FormInterface
    {
        if (!$this->filtersFormBuilder) {
            $this->filtersFormBuilder = call_user_func($this->filtersCallback, $this->getAggregations());
        }
        return $this->filtersFormBuilder;
    }
}
