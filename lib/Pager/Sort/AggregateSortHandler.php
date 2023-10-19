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

class AggregateSortHandler implements SortHandlerInterface
{
    public function __construct(
        protected ChainSortHandler                           $sortsHandler,
    ) {
    }

    public function addSortClause(LocationQuery $pagerQuery, array $sortOptions): void
    {
        foreach ($sortOptions['sorts'] as $sortConfig) {
            $this->sortsHandler->addSortClause($pagerQuery, $sortConfig['type'], $sortConfig['options']);
        }
    }
}
