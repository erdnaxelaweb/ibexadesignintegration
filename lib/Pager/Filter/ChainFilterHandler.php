<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\FilterHandlerInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\NestableFilterHandlerInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AggregationGroup;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionOptions;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChainFilterHandler
{
    /**
     * @var FilterHandlerInterface[]
     */
    protected array $filtersHandler;

    /**
     * @param iterable<FilterHandlerInterface> $filtersHandler
     */
    public function __construct(iterable $filtersHandler)
    {
        foreach ($filtersHandler as $type => $filterHandler) {
            $this->filtersHandler[$type] = $filterHandler;
        }
    }

    public function getAggregation(
        string $filterType,
        string $filterName,
        DefinitionOptions $options,
        array $searchData
    ): ?Aggregation {
        $filterHandler = $this->getFilterHandler($filterType);
        return $filterHandler->getAggregation($filterName, $options, $searchData);
    }

    public function getCriterion(
        string $filterType,
        string $filterName,
        mixed $value,
        DefinitionOptions $options,
        array $searchData
    ): Criterion {
        $filterHandler = $this->getFilterHandler($filterType);
        return $filterHandler->getCriterion(
            $filterName,
            $value,
            $options,
            $searchData
        );
    }

    public function addForm(
        string $filterType,
        FormBuilderInterface $formBuilder,
        string $filterName,
        DefinitionOptions $options,
        AggregationResultCollection $aggregationResultCollection
    ): void {
        $filterHandler = $this->getFilterHandler($filterType);
        $filterHandler->addForm(
            $formBuilder,
            $filterName,
            $options,
            $aggregationResultCollection
        );
    }

    /**
     * @return string[]
     */
    public function getTypes(): array
    {
        return array_keys($this->filtersHandler);
    }

    public function configureOptions(string $filterType, OptionsResolver $optionsResolver): void
    {
        $filterHandler = $this->getFilterHandler($filterType);
        $filterHandler->configureOptions($optionsResolver);
    }

    /**
     * @return array{type: string, options?: array<string, mixed>}
     */
    public function getFakeFormType(string $filterType): array
    {
        $filterHandler = $this->getFilterHandler($filterType);
        return $filterHandler->getFakeFormType();
    }

    /**
     * @param array<string, mixed>|mixed                                 $activeValues
     *
     * @return array<string, string>|string
     */
    public function getValuesLabels(string $filterType, $activeValues, FormInterface $formBuilder): mixed
    {
        $filterHandler = $this->getFilterHandler($filterType);
        return $filterHandler->getValuesLabels($activeValues, $formBuilder);
    }

    public function isNestableFilter(string $filterType): bool
    {
        $filterHandler = $this->getFilterHandler($filterType);
        $reflectionClass = new \ReflectionClass($filterHandler);
        return $reflectionClass instanceof NestableFilterHandlerInterface;
    }



    /**
     * @param array<string, \ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerFilterDefinition> $filterDefinitions
     *
     * @return array<string, Aggregation>
     */
    public function resolveAggregations(array $filterDefinitions, array $searchData): array
    {
        $aggregations = [];
        foreach ($filterDefinitions as $filterName => $filterDefinition) {
            $nestedAggregations = $this->resolveAggregations(
                $filterDefinition->getNestedFilters(),
                $searchData
            );

            // Aggregation
            $aggregation = $this->getAggregation(
                $filterDefinition->getType(),
                $filterName,
                $filterDefinition->getOptions(),
                $searchData
            );

            if ($aggregation instanceof AggregationGroup) {
                foreach ($aggregation->aggregations as $groupAggregationName => $groupAggregation) {
                    $aggregations[$groupAggregationName] = $groupAggregation;
                }
            } elseif ($aggregation) {
                $aggregations[$filterName] = $aggregation;
            }

            if (!empty($nestedAggregations)) {
                if ($aggregation && method_exists($aggregation, 'setNestedAggregations')) {
                    $aggregation->setNestedAggregations($nestedAggregations);
                } else {
                    $aggregations += $nestedAggregations;
                }
            }
        }

        return $aggregations;
    }

    /**
     * @param \ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerFilterDefinition[] $filterDefinitions
     *
     * @return array{
     *     queryCriterions?: array<string, CriterionInterface>,
     *     filtersCriterions?: array<string, CriterionInterface>
     * }
     */
    public function resolveCriterions(array $filterDefinitions, array $searchData): array
    {
        $criterions = [];
        foreach ($filterDefinitions as $filterName => $filterDefinition) {
            $nestedCriterions = $this->resolveCriterions(
                $filterDefinition->getNestedFilters(),
                $searchData
            );

            // Criterion
            $criterionType = $filterDefinition->getCriterionType() === 'query' ? 'queryCriterions' : 'filtersCriterions';
            $filterHandler = $this->getFilterHandler($filterDefinition->getType());

            if ($filterHandler->isCriterionEnabled(
                $filterName,
                $searchData,
                $filterDefinition
            )) {
                $value = $filterHandler->getCriterionValue(
                    $filterName,
                    $searchData,
                    $filterDefinition
                );
                $criterion = $filterHandler->getCriterion(
                    $filterName,
                    $value,
                    $filterDefinition->getOptions(),
                    $searchData
                );
                if ($criterion !== null) {
                    $criterions[$criterionType][$filterName] = $criterion;
                }
            }
            if (!empty($nestedCriterions)) {
                $criterions += $nestedCriterions;
            }
        }
        return $criterions;
    }



    public function getFlattenedFiltersList(PagerDefinition $definition): array
    {
        $filters = [];
        foreach ($definition->getFilters() as $filterDefinitionName => $filterDefinition) {
            $filterHandler = $this->getFilterHandler($filterDefinition->getType());
            if (method_exists($filterHandler, 'getFiltersDefinitions')) {
                $childFiltersDefinitions = $filterHandler->getFiltersDefinitions($filterDefinition->getOptions());
                foreach ($childFiltersDefinitions as $childFiltersDefinitionName => $childFiltersDefinition) {
                    $filters[$childFiltersDefinitionName] = $childFiltersDefinition;
                }
            } else {
                $filters[$filterDefinitionName] = $filterDefinition;
            }
        }
        return $filters;
    }

    protected function getFilterHandler(string $filterType): FilterHandlerInterface
    {
        return $this->filtersHandler[$filterType];
    }
}
