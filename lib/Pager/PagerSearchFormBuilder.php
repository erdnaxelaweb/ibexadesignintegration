<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaHtmlIntegrationBundle/blob/master/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\FilterHandlerInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;

class PagerSearchFormBuilder
{
    /**
     * @var FilterHandlerInterface[]
     */
    protected array $filtersHandler;

    public function __construct(
        iterable                       $filtersHandler,
        protected FormFactoryInterface $formFactory
    ) {
        foreach ($filtersHandler as $type => $filterHandler) {
            $this->filtersHandler[$type] = $filterHandler;
        }
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
            $filterHandler = $this->filtersHandler[$filter['type']];
            $filterHandler->addForm(
                $formFilters,
                $filterName,
                $filter['field'],
                $aggregationResultCollection->get($filterName)
            );
        }
        $builder->add($formFilters);
        if (count($configuration['sorts']) > 1) {
            $builder->add('sort', ChoiceType::class, [
                'choices' => array_flip($configuration['sorts']),
            ]);
        }
        $builder->add('search', SubmitType::class, [
            'label' => 'search',
        ]);
        return $builder->getForm()
            ->createView();
    }
}
