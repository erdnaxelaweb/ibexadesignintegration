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

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractSortHandler implements SortHandlerInterface
{
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->define('sortDirection')
            ->default(Query::SORT_ASC)
            ->allowedTypes('string');
    }
    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    protected function resolveOptions(array $options = []): array
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);

        return $optionsResolver->resolve($options);
    }
}
