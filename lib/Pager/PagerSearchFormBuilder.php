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

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\ChainFilterFormHandler;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;

class PagerSearchFormBuilder
{
    public function __construct(
        protected ChainFilterFormHandler                           $formHandler,
        protected FormFactoryInterface $formFactory
    ) {
    }

    public function build(
        array                       $configuration,
        AggregationResultCollection $aggregationResultCollection,
        SearchData                     $searchData
    ) {
        $builder = $this->formFactory->createBuilder(FormType::class, $searchData, [
            'method' => 'GET',
        ]);
        $formFilters = $builder->create('filters', FormType::class, [
            'compound' => true,
        ]);

        foreach ($configuration['filters'] as $filterName => $filter) {
            $this->formHandler->addForm(
                $formFilters,
                $filter['formType'],
                $filterName,
                $filter['field'],
                $aggregationResultCollection->get($filterName)
            );
        }
        $builder->add($formFilters);
        if (count($configuration['sorts']) > 1) {
            $builder->add('sort', ChoiceType::class, [
                'choices' => array_combine(array_keys($configuration['sorts']), array_keys($configuration['sorts'])),
            ]);
        }
        $builder->add('search', SubmitType::class, [
            'label' => 'search',
        ]);
        return $builder->getForm()
            ->createView();
    }
}
