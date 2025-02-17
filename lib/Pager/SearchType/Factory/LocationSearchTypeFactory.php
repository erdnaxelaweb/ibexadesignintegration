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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\Factory;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\LocationSearchType;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\SearchTypeInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use Symfony\Component\HttpFoundation\Request;

class LocationSearchTypeFactory extends ContentSearchTypeFactory
{
    public function __invoke(
        string     $searchFormName,
        array      $configuration,
        Request    $request,
        SearchData $defaultSearchData = new SearchData()
    ): SearchTypeInterface {
        return new LocationSearchType(
            $this->searchService,
            $this->contentTransformer,
            $this->pagerSearchFormBuilder,
            $this->pagerActiveFiltersListBuilder,
            $searchFormName,
            $configuration,
            $request,
            $defaultSearchData
        );
    }
}
