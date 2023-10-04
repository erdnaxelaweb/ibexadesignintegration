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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\CustomField;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Novactive\EzSolrSearchExtra\Query\Aggregation\RawTermAggregation;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\FilterTag;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class ChoiceFilterHandler implements FilterHandlerInterface
{
    public function getCriterion( string $filterName, string $field, $value ): Criterion
    {
        $operator = is_array( $value ) ? Operator::IN : Operator::EQ;
        $criterion = new CustomField( $field, $operator, $value );
        return new FilterTag( $filterName, $criterion );
    }

    public function getAggregation(string $filterName,  string $field ): RawTermAggregation
    {
        return new RawTermAggregation( $filterName, $field, [$filterName] );
    }

    public function addForm(
        FormBuilderInterface $formBuilder,
        string               $filterName,
        string               $field,
        AggregationResult    $aggregationResult
    ): void
    {
        $options = $this->getFormOptions();
        $options['label'] = sprintf('searchform.%s', $field);
        $options['required'] = false;
        $options['choices'] = [];
        /** @var \Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResultEntry $value */
        foreach ( $aggregationResult->getEntries() as $value )
        {
            $options['choices'][$value->getKey()] = $value->getKey();
        }
        $formBuilder->add(
            $filterName,
            ChoiceType::class,
            $options
        );
    }

    abstract protected function getFormOptions():array;
}
