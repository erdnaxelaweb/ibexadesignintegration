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

use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;

interface SearchTypeInterface
{
    public function getQuery(): Query;

    public function getSearchData(): SearchData;

    public function getAdapter(): PagerAdapterInterface;
}
