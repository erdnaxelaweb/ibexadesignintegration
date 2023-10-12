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

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\CustomField;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Core\Search\Common\FieldRegistry;
use Novactive\EzSolrSearchExtra\Query\Aggregation\RawTermAggregation;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\FilterTag;

class FieldFilterHandler implements FilterHandlerInterface
{
    public function __construct(
        protected FieldRegistry $fieldRegistry
    ) {
    }

    public function getCriterion(string $filterName, string $field, $value): Criterion
    {
        $operator = is_array($value) ? Operator::IN : Operator::EQ;
        $criterion = new CustomField( $this->getFieldName( $filterName), $operator, $value);
        return new FilterTag( $filterName, $criterion);
    }

    public function getAggregation(string $filterName): RawTermAggregation
    {
        return new RawTermAggregation( $filterName, $this->getFieldName( $filterName), [ $filterName]);
    }

    protected function getFieldName(string $field)
    {
        $fieldIdentifier = ltrim($field, 'fields.');
        dd($this->fieldRegistry->getType('ezselection')->getDefaultMatchField());
    }

    public function support(string $field): bool
    {
        return true;
    }
}
