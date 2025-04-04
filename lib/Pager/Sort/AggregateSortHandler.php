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

class AggregateSortHandler extends AbstractSortHandler
{
    public function __construct(
        protected ChainSortHandler $sortsHandler,
    ) {
    }

    public function addSortClause(Query $pagerQuery, DefinitionOptions $sortOptions): void
    {
        foreach ($sortOptions['sorts'] as $sortConfig) {
            $this->sortsHandler->addSortClause($pagerQuery, $sortConfig['type'], $sortConfig['options']);
        }
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->remove('sortDirection');
        $optionsResolver->define('sorts')
            ->required()
            ->allowedTypes('array');
    }
}
