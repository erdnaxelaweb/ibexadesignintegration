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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFilterHandler implements FilterHandlerInterface
{
    public function isCriterionEnabled(
        string $filterName,
        array $searchData,
        PagerFilterDefinition $filterDefinition
    ): bool {
        return (!empty($searchData[$filterName])) ||
               $filterDefinition->hasDefaultValue();
    }

    public function getCriterionValue(
        string $filterName,
        array $searchData,
        PagerFilterDefinition $filterDefinition
    ): mixed {
        return (!empty($searchData[$filterName])) ?
            $searchData[$filterName] :
            $filterDefinition->getDefaultValue();
    }

    public function getAggregation(string $filterName, DefinitionOptions $options, array $searchData): ?Aggregation
    {
        return null;
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
    }

    public function getValuesLabels($activeValues, FormInterface $formBuilder): mixed
    {
        $activeValues = (array) $activeValues;
        return array_combine($activeValues, $activeValues);
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
