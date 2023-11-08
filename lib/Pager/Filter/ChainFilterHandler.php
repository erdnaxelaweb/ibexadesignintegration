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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChainFilterHandler
{
    /**
     * @var \ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\FilterHandlerInterface[]
     */
    protected array $filtersHandler;

    public function __construct(
        iterable $filtersHandler,
    ) {
        foreach ($filtersHandler as $type => $filterHandler) {
            $this->filtersHandler[$type] = $filterHandler;
        }
    }

    public function getAggregation(string $filterType, string $filterName, array $options = []): ?Aggregation
    {
        $filterHandler = $this->filtersHandler[$filterType];
        return $filterHandler->getAggregation($filterName, $options);
    }

    public function getCriterion(string $filterType, string $filterName, $value, array $options = []): Criterion
    {
        $filterHandler = $this->filtersHandler[$filterType];
        return $filterHandler->getCriterion($filterName, $value, $options);
    }

    public function addForm(
        string               $filterType,
        FormBuilderInterface $formBuilder,
        string               $filterName,
        ?AggregationResult   $aggregationResult = null,
        array                $options = []
    ): void {
        $filterHandler = $this->filtersHandler[$filterType];
        $filterHandler->addForm($formBuilder, $filterName, $aggregationResult, $options);
    }

    public function configureOptions(string $filterType, OptionsResolver $optionsResolver): void
    {
        $filterHandler = $this->filtersHandler[$filterType];
        $filterHandler->configureOptions($optionsResolver);
    }

    public function getTypes(): array
    {
        return array_keys($this->filtersHandler);
    }

    public function getFakeFormType(string $filterType): array
    {
        $filterHandler = $this->filtersHandler[$filterType];
        return $filterHandler->getFakeFormType();
    }

    public function getValuesLabels(
        string $filterType,
        array $activeValues,
        FormBuilderInterface $formBuilder
    ): array {
        $filterHandler = $this->filtersHandler[$filterType];
        return $filterHandler->getValuesLabels($activeValues, $formBuilder);
    }
}
