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

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\ChainFilterCriterionHandler;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\ChainFilterFormHandler;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Sort\ChainSortHandler;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\PagerConfigurationManager as BasePagerConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Fake\Generator\SearchFormGenerator;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PagerConfigurationManager extends BasePagerConfigurationManager
{
    public function __construct(
        protected ChainFilterFormHandler                          $filterFormHandler,
        protected ChainFilterCriterionHandler                          $filterCriterionHandler,
        protected ChainSortHandler                          $sortHandler,
        array $definitions,
        SearchFormGenerator $searchFormGenerator
    ) {
        parent::__construct($definitions, $searchFormGenerator);
    }

    protected function configureFilterOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->define('field')
            ->required()
            ->allowedTypes('string');

        $optionsResolver->define('formType')
            ->required()
            ->allowedTypes('string')
            ->allowedValues(...$this->filterFormHandler->getTypes());

        $optionsResolver->define('criterionType')
            ->default('raw')
            ->allowedTypes('string')
            ->allowedValues(...$this->filterCriterionHandler->getTypes());
    }

    protected function configureSortOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->define('options')
            ->default([])
            ->allowedTypes('array');

        $optionsResolver->define('type')
            ->required()
            ->allowedTypes('string')
            ->allowedValues(...$this->sortHandler->getTypes());
    }
}
