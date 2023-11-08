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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\ChainFilterHandler;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

class PagerSearchFormBuilder
{
    public function __construct(
        protected ChainFilterHandler            $filterHandler,
        protected FormFactoryInterface          $formFactory
    ) {
    }

    public function build(
        string                      $type,
        array                       $pagerConfiguration,
        AggregationResultCollection $aggregationResultCollection,
        SearchData                  $searchData
    ): FormBuilderInterface {
        $builder = $this->formFactory->createNamedBuilder($type, FormType::class, $searchData, [
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
        $formFilters = $builder->create('filters', FormType::class, [
            'compound' => true,
            'block_prefix' => 'filters',
        ]);

        foreach ($pagerConfiguration['filters'] as $filterName => $filter) {
            $this->filterHandler->addForm(
                $filter['type'],
                $formFilters,
                $filterName,
                $aggregationResultCollection->has($filterName) ?
                    $aggregationResultCollection->get($filterName) : null,
                $filter['options'],
            );
        }
        $builder->add($formFilters);
        if (count($pagerConfiguration['sorts']) > 1) {
            $builder->add('sort', ChoiceType::class, [
                'choices' => array_combine(
                    array_keys($pagerConfiguration['sorts']),
                    array_keys($pagerConfiguration['sorts'])
                ),
                'block_prefix' => 'sort',
            ]);
        }
        $builder->add('search', SubmitType::class, [
            'label' => 'search',
        ]);

        return $builder;
    }
}
