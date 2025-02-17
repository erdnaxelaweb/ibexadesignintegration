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

use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;

interface SearchTypeInterface
{
    public function getQuery(): Query;

    public function getSearchData(): SearchData;

    public function getAdapter(): PagerAdapterInterface;
}
