<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerFilterDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Definition\Transformer\PagerFilterDefinitionTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\ChainFilterHandler;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AggregationGroup;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionOptions;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use InvalidArgumentException;
use Novactive\EzSolrSearchExtra\Query\Aggregation\RawTermAggregation;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\FilterTag;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\ParentTag;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParentFilterHandler implements FilterHandlerInterface
{
    public function __construct(
        protected ChainFilterHandler $chainFilterHandler,
        protected PagerFilterDefinitionTransformer $pagerFilterDefinitionTransformer
    ) {
    }

    public function addForm(
        FormBuilderInterface $formBuilder,
        string               $filterName,
        DefinitionOptions    $options,
        AggregationResultCollection $aggregationResultCollection
    ): void {
        $childFiltersDefinitions = $this->getFiltersDefinitions($options);
        foreach ($childFiltersDefinitions as $childFilterDefinitionName => $childFilterDefinition) {
            $this->chainFilterHandler->addForm(
                $childFilterDefinition->getType(),
                $formBuilder,
                $childFilterDefinitionName,
                $childFilterDefinition->getOptions(),
                $aggregationResultCollection,
            );
        }
    }

    public function getCriterion(string $filterName, mixed $value, DefinitionOptions $options, array $searchData): ?Criterion
    {
        $childCriterions = $this->getChildCriterions($filterName, $options, $searchData);
        if (empty($childCriterions)) {
            return null;
        }

        foreach ($childCriterions as $criterionName => $criterion) {
            $childCriterions[$criterionName] = new ParentTag($options->get('which'), $criterion);
        }


        return new Criterion\LogicalAnd(
            $childCriterions
        );
    }

    public function getAggregation(string $filterName, DefinitionOptions $options, array $searchData): ?Aggregation
    {
        $childFiltersDefinitions = $this->getFiltersDefinitions($options);
        $childAggregations = $this->chainFilterHandler->resolveAggregations(
            $childFiltersDefinitions,
            $searchData
        );

        $childCriterions = $this->getChildCriterions($filterName, $options, $searchData);
        foreach ($childAggregations as $childAggregationName => $childAggregation) {
            if (!$childAggregation instanceof RawTermAggregation) {
                throw new InvalidArgumentException('Only ' . RawTermAggregation::class . ' is supported');
            }

            $childAggregation->nestedAggregations["parent_count"] = "uniqueBlock(_root_)";
            $childAggregation->domain['blockChildren'] = "{!v='*:* -_nest_path_:*'}";

            foreach ($childCriterions as $childCriterionName => $childCriterion) {
                if ($childCriterionName !== $childAggregationName) {
                    $childAggregation->domain['filter'][] = $childCriterion;
                }
            }
        }

        return new AggregationGroup($childAggregations);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->define('which')
            ->default('*:* -_nest_path_:*')
            ->allowedTypes('string');

        $optionsResolver->define('filters')
            ->default([])
            ->allowedTypes('array')
            ->normalize(function (Options $options, $filtersOptions) {
                if (empty($filtersOptions)) {
                    return [];
                }

                $nestedFilters = [];
                foreach ($filtersOptions as $filterIdentifier => $nestedFilterOptions) {
                    $optionsResolver = new OptionsResolver();
                    $this->pagerFilterDefinitionTransformer->configureOptions($optionsResolver, $nestedFilterOptions);
                    $nestedFilters[$filterIdentifier] = $optionsResolver->resolve($nestedFilterOptions);
                }
                return $nestedFilters;
            });
    }

    public function getFakeFormType(): array
    {
        return [];
    }

    public function getValuesLabels($activeValues, FormInterface $formBuilder): mixed
    {
    }

    /**
     * @return array<string, PagerFilterDefinition>
     */
    public function getFiltersDefinitions(DefinitionOptions $options): array
    {
        $filtersHash = $options->get('filters');

        $definitions = [];
        foreach ($filtersHash as $filterIdentifier => $filterOptions) {
            $definitions[$filterIdentifier] = $this->pagerFilterDefinitionTransformer->fromHash(
                [
                    'identifier' => $filterIdentifier,
                    'hash' => $filterOptions,
                ]
            );
        }
        return $definitions;
    }

    public function isCriterionEnabled(
        string                $filterName,
        array            $searchData,
        PagerFilterDefinition $filterDefinition
    ): bool {
        return true;
    }

    public function getCriterionValue(
        string                $filterName,
        array            $searchData,
        PagerFilterDefinition $filterDefinition
    ): mixed {
        return null;
    }

    /**
     * @return Criterion[]
     */
    protected function getChildCriterions(string $filterName, DefinitionOptions $options, array $searchData): array
    {
        $childFiltersDefinitions = $this->getFiltersDefinitions($options);
        $childCriterions = $this->chainFilterHandler->resolveCriterions(
            $childFiltersDefinitions,
            $searchData
        );
        $criterions = $childCriterions['filtersCriterions'] ?? [];
        foreach ($criterions as $criterionName => $criterion) {
            if ($criterion instanceof FilterTag) {
                //                $criterions[$criterionName] = $criterion->criterion;
            }
        }
        return $criterions;
    }
}
