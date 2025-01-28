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

declare(strict_types=1);

namespace ErdnaxelaWeb\IbexaDesignIntegration\Configuration;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\ChainFilterHandler;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Sort\ChainSortHandler;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\PagerConfigurationManager as BasePagerConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Fake\Generator\SearchFormGenerator;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PagerConfigurationManager extends BasePagerConfigurationManager
{
    protected array $availableSearchTypes;

    public function __construct(
        protected ChainFilterHandler $filterHandler,
        protected ChainSortHandler   $sortHandler,
        array                        $definitions,
        SearchFormGenerator          $searchFormGenerator,
        iterable $searchTypeFactories,
    ) {
        parent::__construct($definitions, $searchFormGenerator);
        $this->availableSearchTypes = [];
        foreach ($searchTypeFactories as $type => $searchTypeFactory) {
            $this->availableSearchTypes[] = $type;
        }
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->define('searchType')
            ->default('location')
            ->allowedTypes('string')
            ->allowedValues(...$this->availableSearchTypes);

        parent::configureOptions($optionsResolver);
    }

    protected function configureFilterOptions(OptionsResolver $optionsResolver, string $filterType): void
    {
        $optionsResolver->define('type')
            ->required()
            ->allowedTypes('string')
            ->allowedValues(...$this->filterHandler->getTypes());

        $optionsResolver->define('criterionType')
            ->default('filter')
            ->allowedTypes('string')
            ->allowedValues('filter', 'query');

        $optionsResolver->define('options')
            ->default([])
            ->normalize(function (Options $options, $fieldDefinitionOptions) {
                $optionsResolver = new OptionsResolver();
                $this->filterHandler->configureOptions($options['type'], $optionsResolver);
                return $this->resolveOptions($options['type'], $optionsResolver, $fieldDefinitionOptions);
            })
            ->allowedTypes('array');

        if($this->filterHandler->isNestableFilter($filterType)) {
            $optionsResolver->define('nested')
                ->default([])
                ->normalize(function ( Options $options, $nestedFiltersOptions) {
                    if(empty($nestedFiltersOptions)) {
                        return [];
                    }

                    $nestedFilters = [];
                    foreach ( $nestedFiltersOptions as $filterIdentifier => $nestedFilterOptions) {
                        $optionsResolver = new OptionsResolver();
                        $this->configureFilterOptions($optionsResolver, $nestedFilterOptions['type'] ?? '');

                        $nestedFilterOptions['options']['is_nested'] = true;

                        $nestedFilters[$filterIdentifier] = $this->resolveOptions(
                            $filterIdentifier,
                            $optionsResolver,
                            $nestedFilterOptions
                        );
                    }
                    return $nestedFilters;
                })
                ->allowedTypes('array');
        }
    }

    protected function configureSortOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->define('options')
            ->default([])
            ->normalize(function (Options $options, $fieldDefinitionOptions) {
                $optionsResolver = new OptionsResolver();
                $this->sortHandler->configureOptions($options['type'], $optionsResolver);
                return $this->resolveOptions($options['type'], $optionsResolver, $fieldDefinitionOptions);
            })
            ->allowedTypes('array');

        $optionsResolver->define('type')
            ->required()
            ->allowedTypes('string')
            ->allowedValues(...$this->sortHandler->getTypes());
    }
}
