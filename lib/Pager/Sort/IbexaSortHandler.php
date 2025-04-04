<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
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
