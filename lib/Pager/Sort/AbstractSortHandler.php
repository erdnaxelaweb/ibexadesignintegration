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

abstract class AbstractSortHandler implements SortHandlerInterface
{
    protected function resolveOptions(array $options = []): array
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);

        return $optionsResolver->resolve($options);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->define('sortDirection')
            ->default(Query::SORT_ASC)
            ->allowedTypes('string');
    }
}
