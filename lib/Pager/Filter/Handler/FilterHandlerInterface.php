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
use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionOptions;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface FilterHandlerInterface
{
    public function addForm(
        FormBuilderInterface $formBuilder,
        string $filterName,
        DefinitionOptions $options,
        AggregationResultCollection $aggregationResultCollection
    ): void;

    public function isCriterionEnabled(
        string $filterName,
        array $searchData,
        PagerFilterDefinition $filterDefinition
    ): bool;

    public function getCriterionValue(
        string $filterName,
        array $searchData,
        PagerFilterDefinition $filterDefinition
    ): mixed;

    public function getCriterion(string $filterName, mixed $value, DefinitionOptions $options, array $searchData): ?Criterion;

    public function getAggregation(string $filterName, DefinitionOptions $options, array $searchData): ?Aggregation;

    public function configureOptions(OptionsResolver $optionsResolver): void;

    /**
     * @return array{type: string, options?: array<string, mixed>}
     */
    public function getFakeFormType(): array;

    /**
     * @param array<string, mixed>|mixed                                 $activeValues
     *
     * @return array<string, string>|string
     */
    public function getValuesLabels($activeValues, FormInterface $formBuilder): mixed;
}
