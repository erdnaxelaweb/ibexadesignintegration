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
use Ibexa\Core\Search\Common\FieldRegistry;
use Novactive\EzSolrSearchExtra\Query\Aggregation\RawTermAggregation;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\FilterTag;

class FieldFilterHandler implements FilterHandlerInterface
{
    public function __construct(
        protected FieldRegistry $fieldRegistry
    ) {
    }

    public function getCriterion(string $field, $value, bool $isMultiple = false): Criterion
    {
        $operator = is_array($value) ? Operator::IN : Operator::EQ;
        $criterion = new CustomField($this->getFieldName($field), $operator, $value);
        if ($isMultiple) {
            return new FilterTag($field, $criterion);
        }
        return $criterion;
    }

    public function getAggregation(string $field): RawTermAggregation
    {
        return new RawTermAggregation($field, $this->getFieldName($field), [$field]);
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
