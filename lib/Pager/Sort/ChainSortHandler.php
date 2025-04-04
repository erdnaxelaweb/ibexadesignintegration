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
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChainSortHandler
{
    /**
     * @var SortHandlerInterface[]
     */
    protected array $sortsHandler;

    /**
     * @param iterable<SortHandlerInterface> $sortsHandler
     */
    public function __construct(iterable $sortsHandler)
    {
        foreach ($sortsHandler as $type => $sortHandler) {
            $this->sortsHandler[$type] = $sortHandler;
        }
    }

    public function addSortClause(Query $pagerQuery, string $sortType, DefinitionOptions $sortOptions): void
    {
        $sortHandler = $this->sortsHandler[$sortType];
        $sortHandler->addSortClause($pagerQuery, $sortOptions);
    }

    public function configureOptions(string $filterType, OptionsResolver $optionsResolver): void
    {
        $sortHandler = $this->sortsHandler[$filterType];
        $sortHandler->configureOptions($optionsResolver);
    }

    /**
     * @return string[]
     */
    public function getTypes(): array
    {
        return array_keys($this->sortsHandler);
    }
}
