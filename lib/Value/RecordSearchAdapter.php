<?php
declare( strict_types=1 );

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class RecordSearchAdapter implements PagerAdapterInterface
{
    private ?FormInterface $filtersFormBuilder = null;

    /**
     * @param callable(AggregationResultCollection): FormInterface $filtersCallback
     * @param callable(FormInterface): \Knp\Menu\ItemInterface[] $activeFiltersCallback
     */
    public function __construct(
        protected $filtersCallback,
        protected $activeFiltersCallback,
    )
    {
    }

    public function getNbResults()
    {
        // TODO: Implement getNbResults() method.
    }

    public function getSlice( $offset, $length )
    {
        // TODO: Implement getSlice() method.
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
