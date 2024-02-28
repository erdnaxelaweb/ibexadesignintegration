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

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AggregateSortHandler extends AbstractSortHandler
{
    public function __construct(
        protected ChainSortHandler                           $sortsHandler,
    ) {
    }

    public function addSortClause(Query $pagerQuery, array $sortOptions): void
    {
        $sortOptions = $this->resolveOptions($sortOptions);
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
