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

use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionOptions;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;

class IbexaSortHandler extends AbstractSortHandler
{
    public function __construct(
        protected string $sortClauseClass
    ) {
    }

    public function addSortClause(Query $pagerQuery, DefinitionOptions $sortOptions): void
    {
        $pagerQuery->sortClauses[] = new $this->sortClauseClass(...$sortOptions->toArray());
    }
}
