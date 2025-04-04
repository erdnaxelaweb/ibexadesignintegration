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

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\FilterHandlerInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\NestableFilterHandlerInterface;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionOptions;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
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

    public function getAggregation(string $filterType, string $filterName, DefinitionOptions $options): ?Aggregation
    {
        $filterHandler = $this->getFilterHandler($filterType);
        return $filterHandler->getAggregation($filterName, $options);
    }

    public function getCriterion(
        string $filterType,
        string $filterName,
        mixed $value,
        DefinitionOptions $options
    ): Criterion {
        $filterHandler = $this->getFilterHandler($filterType);
        return $filterHandler->getCriterion($filterName, $value, $options);
    }

    public function addForm(
        string $filterType,
        FormBuilderInterface $formBuilder,
        string $filterName,
        DefinitionOptions $options,
        ?AggregationResult $aggregationResult = null,
    ): void {
        $filterHandler = $this->getFilterHandler($filterType);
        $filterHandler->addForm($formBuilder, $filterName, $options, $aggregationResult);
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
     * @param array<string, mixed>                                 $activeValues
     *
     * @return array<string, string>
     */
    public function getValuesLabels(string $filterType, array $activeValues, FormInterface $formBuilder): array
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

    protected function getFilterHandler(string $filterType): FilterHandlerInterface
    {
        return $this->filtersHandler[$filterType];
    }
}
