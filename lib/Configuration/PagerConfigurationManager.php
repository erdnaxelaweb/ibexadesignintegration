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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Configuration;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\ChainFilterHandler;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Sort\ChainSortHandler;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchAdapter;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\PagerConfigurationManager as BasePagerConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Fake\Generator\SearchFormGenerator;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PagerConfigurationManager extends BasePagerConfigurationManager
{
    public function __construct(
        protected ChainFilterHandler $filterHandler,
        protected ChainSortHandler   $sortHandler,
        array                        $definitions,
        SearchFormGenerator          $searchFormGenerator
    ) {
        parent::__construct($definitions, $searchFormGenerator);
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->define('searchType')
            ->default(SearchAdapter::SEARCH_TYPE_LOCATION)
            ->allowedTypes('string')
            ->allowedValues(SearchAdapter::SEARCH_TYPE_LOCATION, SearchAdapter::SEARCH_TYPE_CONTENT);

        parent::configureOptions($optionsResolver);
    }

    protected function configureFilterOptions(OptionsResolver $optionsResolver): void
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
