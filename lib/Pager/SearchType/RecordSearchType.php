<?php
declare( strict_types=1 );

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\RecordSearchAdapter;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;

class RecordSearchType extends AbstractSearchType
{
    public function getAdapter(): PagerAdapterInterface
    {
        return new RecordSearchAdapter(
            [$this, 'getFiltersForm'],
            [$this, 'getActiveFilters']
        );
    }

    protected function initializeQuery(): void
    {
        $this->query = new Query();
    }
}
