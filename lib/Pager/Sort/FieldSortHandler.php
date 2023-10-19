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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Sort;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;

class FieldSortHandler implements SortHandlerInterface
{
    public function addSortClause(LocationQuery $pagerQuery, array $sortOptions): void
    {
        $pagerQuery->sortClauses[] = new SortClause\Field(...$sortOptions);
    }
}
