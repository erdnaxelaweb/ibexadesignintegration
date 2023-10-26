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

class IbexaSortHandler extends AbstractSortHandler
{
    public function __construct(
        protected string $sortClauseClass
    ) {
    }

    public function addSortClause(LocationQuery $pagerQuery, array $sortOptions): void
    {
        $pagerQuery->sortClauses[] = new $this->sortClauseClass(...$sortOptions);
    }
}
