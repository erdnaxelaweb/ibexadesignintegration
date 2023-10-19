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

class ChainSortHandler
{
    /**
     * @var SortHandlerInterface[]
     */
    protected array $sortsHandler;

    public function __construct(
        iterable                           $sortsHandler,
    ) {
        foreach ($sortsHandler as $type => $sortHandler) {
            $this->sortsHandler[$type] = $sortHandler;
        }
    }

    public function addSortClause(LocationQuery $pagerQuery, string $sortType, array $sortOptions): void
    {
        $sortHandler = $this->sortsHandler[$sortType];
        $sortClause = $sortHandler->addSortClause($pagerQuery, $sortOptions);
    }

    public function getTypes(): array
    {
        return array_keys($this->sortsHandler);
    }
}
