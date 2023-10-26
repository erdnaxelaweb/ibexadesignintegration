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

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\CustomField;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Novactive\EzSolrSearchExtra\Query\Aggregation\RawTermAggregation;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\FilterTag;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomFieldFilterHandler extends AbstractFilterHandler
{
    /**
     * @param \Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResult $aggregationResult
     */
    public function addForm(
        FormBuilderInterface $formBuilder,
        string               $filterName,
        ?AggregationResult   $aggregationResult = null,
        array                $options = []
    ): void {
        $options = $this->resolveOptions($options);

        $formOptions['label'] = sprintf('searchform.%s', $filterName);
        $formOptions['required'] = false;
        $formOptions['multiple'] = $options['multiple'];
        $formOptions['expanded'] = $options['expanded'];
        $formOptions['choices'] = [];
        if ($aggregationResult) {
            foreach ($aggregationResult->getEntries() as $entry) {
                $formOptions['choices'][$entry->getName()] = $entry->getKey();
            }
        }
        $formBuilder->add($filterName, ChoiceType::class, $formOptions);
    }

    public function getCriterion(string $filterName, $value, array $options = []): Criterion
    {
        $options = $this->resolveOptions($options);
        $operator = is_array($value) ? Operator::IN : Operator::EQ;
        $criterion = new CustomField($options['field'], $operator, $value);
        if ($options['multiple'] === false) {
            return $criterion;
        }
        return new FilterTag($filterName, $criterion);
    }

    public function getAggregation(string $filterName, array $options = []): ?Aggregation
    {
        $options = $this->resolveOptions($options);
        return new RawTermAggregation($filterName, $options['field'], [$filterName]);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->define('field')
            ->required()
            ->allowedTypes('string');
        $optionsResolver->define('multiple')
            ->default(false)
            ->allowedTypes('bool');
        $optionsResolver->define('expanded')
            ->default(false)
            ->allowedTypes('bool');
    }
}
