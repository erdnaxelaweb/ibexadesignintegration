<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaHtmlIntegrationBundle/blob/master/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Sort;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;

class PublishDateSortHandler implements SortHandlerInterface
{
    public function getSortClause(array $sortConfig): SortClause
    {
        return new SortClause\DatePublished(...$sortConfig);
    }
}
