<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Definition\Transformer;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerFilterDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\ChainFilterHandler;
use ErdnaxelaWeb\StaticFakeDesign\Definition\AbstractLazyDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionInterface;
use ErdnaxelaWeb\StaticFakeDesign\Definition\Transformer\PagerFilterDefinitionTransformer as NativePagerFilterDefinitionTransformer;
use ErdnaxelaWeb\StaticFakeDesign\Fake\Generator\SearchFormGenerator;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\VarExporter\Instantiator;

class PagerFilterDefinitionTransformer extends NativePagerFilterDefinitionTransformer
{
    public function __construct(
        SearchFormGenerator $searchFormGenerator,
        protected ChainFilterHandler $filterHandler,
    ) {
        parent::__construct($searchFormGenerator);
    }

    public function configureOptions(OptionsResolver $optionsResolver, array $options): void
    {
        parent::configureOptions($optionsResolver, $options);
        $optionsResolver->setAllowedValues('type', $this->filterHandler->getTypes());

        $optionsResolver->setNormalizer(
            'options',
            function (Options $options, $fieldDefinitionOptions) {
                $optionsResolver = new OptionsResolver();
                $this->filterHandler->configureOptions($options['type'], $optionsResolver);
                return $optionsResolver->resolve($fieldDefinitionOptions);
            }
        );

        $optionsResolver->define('criterionType')
                        ->default('filter')
                        ->allowedTypes('string')
                        ->allowedValues('filter', 'query');

        if ($this->filterHandler->isNestableFilter($options['type'] ?? '')) {
            $optionsResolver->define('nested')
                ->default([])
                ->normalize(function (Options $options, $nestedFiltersOptions) {
                    if (empty($nestedFiltersOptions)) {
                        return [];
                    }

                    $nestedFilters = [];
                    foreach ($nestedFiltersOptions as $filterIdentifier => $nestedFilterOptions) {
                        $optionsResolver = new OptionsResolver();
                        $this->configureOptions($optionsResolver, $nestedFilterOptions);
                        $nestedFilterOptions['options']['is_nested'] = true;
                        $nestedFilters[$filterIdentifier] = $optionsResolver->resolve($nestedFilterOptions);
                    }
                    return $nestedFilters;
                })
                ->allowedTypes('array');
        }
    }

    public function fromHash(array $hash): PagerFilterDefinition
    {
        return $this->lazyFromHash(Instantiator::instantiate(PagerFilterDefinition::class, [
            'identifier' => $hash['identifier'],
        ]), $hash['hash']);
    }

    /**
     * @param PagerFilterDefinition $definition
     */
    public function toHash(DefinitionInterface $definition): array
    {
        $hash = parent::toHash($definition);
        $hash['hash'] += [
            'criterionType' => $definition->getCriterionType(),
            'nested' => array_map(
                function (PagerFilterDefinition $nestedFilterDefinition) {
                    return $this->toHash($nestedFilterDefinition);
                },
                $definition->getNestedFilters()
            ),
        ];
        return $hash;
    }

    protected function lazyInitialize(AbstractLazyDefinition $instance, array $options): DefinitionInterface
    {
        if (isset($options['nested'])) {
            $nestedFilters = [];
            foreach ($options['nested'] as $filterIdentifier => $nestedFilterOptions) {
                $nestedFilters[$filterIdentifier] = $this->fromHash([
                    'identifier' => $filterIdentifier,
                    'hash' => $nestedFilterOptions,
                ]);
            }
            $options['nested'] = $nestedFilters;
        }
        return parent::lazyInitialize($instance, $options);
    }
}
