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
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldSortHandler extends AbstractSortHandler
{
    public function addSortClause(Query $pagerQuery, array $sortOptions): void
    {
        $pagerQuery->sortClauses[] = new SortClause\Field(...$sortOptions);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->define('fieldIdentifier')
            ->required()
            ->allowedTypes('string');
        $optionsResolver->define('typeIdentifier')
            ->required()
            ->allowedTypes('string');
    }
}
