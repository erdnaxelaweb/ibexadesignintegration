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

use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;

class PrioritySortHandler implements SortHandlerInterface
{
    public function getSortClause(array $sortConfig): SortClause
    {
        return new SortClause\Location\Priority(...$sortConfig);
    }
}
