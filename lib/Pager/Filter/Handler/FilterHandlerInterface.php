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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler;

use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionOptions;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface FilterHandlerInterface
{
    public function addForm(
        FormBuilderInterface $formBuilder,
        string $filterName,
        DefinitionOptions $options,
        ?AggregationResult $aggregationResult = null,
    ): void;

    public function getCriterion(string $filterName, mixed $value, DefinitionOptions $options): Criterion;

    public function getAggregation(string $filterName, DefinitionOptions $options): ?Aggregation;

    public function configureOptions(OptionsResolver $optionsResolver): void;

    public function getFakeFormType(): array;

    public function getValuesLabels(array $activeValues, FormInterface $formBuilder): array;
}
