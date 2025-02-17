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

use ErdnaxelaWeb\IbexaDesignIntegration\Value\LocationSearchAdapter;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;

class LocationSearchType extends ContentSearchType
{
    public function initializeQuery(): void
    {
        $this->query = new LocationQuery();
    }

    public function getAdapter(): PagerAdapterInterface
    {
        return new LocationSearchAdapter(
            $this->query,
            $this->searchService,
            $this->contentTransformer,
            [$this, 'getFiltersForm'],
            [$this, 'getActiveFilters']
        );
    }
}
