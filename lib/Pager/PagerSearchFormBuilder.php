<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerFilterDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\ChainFilterHandler;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

class PagerSearchFormBuilder
{
    public function __construct(
        protected ChainFilterHandler $filterHandler,
        protected FormFactoryInterface $formFactory
    ) {
    }

    public function build(
        string $type,
        PagerDefinition $pagerDefinition,
        AggregationResultCollection $aggregationResultCollection,
        SearchData $searchData
    ): FormBuilderInterface {
        $builder = $this->formFactory->createNamedBuilder($type, FormType::class, $searchData, [
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
        $formFilters = $builder->create('filters', FormType::class, [
            'compound' => true,
            'block_prefix' => 'filters',
        ]);

        foreach ($pagerDefinition->getFilters() as $filterName => $filter) {
            $this->resolveFilter(
                $formFilters,
                $filterName,
                $filter,
                $aggregationResultCollection
            );
        }

        $builder->add($formFilters);
        if (count($pagerDefinition->getSorts()) > 1) {
            $builder->add('sort', ChoiceType::class, [
                'choices' => array_combine(
                    array_keys($pagerDefinition->getSorts()),
                    array_keys($pagerDefinition->getSorts())
                ),
                'block_prefix' => 'sort',
            ]);
        }

        return $builder;
    }

    protected function resolveFilter(
        FormBuilderInterface $formFilters,
        string $filterName,
        PagerFilterDefinition $filterDefinition,
        AggregationResultCollection $aggregationResultCollection
    ): void {
        $this->filterHandler->addForm(
            $filterDefinition->getType(),
            $formFilters,
            $filterName,
            $filterDefinition->getOptions(),
            $aggregationResultCollection,
        );

        if (!empty($filterDefinition->getNestedFilters())) {
            foreach ($filterDefinition->getNestedFilters() as $filterName => $filterDefinition) {
                $this->resolveFilter(
                    $formFilters,
                    $filterName,
                    $filterDefinition,
                    $aggregationResultCollection
                );
            }
        }
    }
}
