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

use ErdnaxelaWeb\StaticFakeDesign\Fake\FakerGenerator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\CustomField;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Novactive\EzSolrSearchExtra\Query\Aggregation\RawTermAggregation;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\FilterTag;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomFieldFilterHandler extends AbstractFilterHandler
{
    public function __construct(
        protected FakerGenerator $fakerGenerator
    ) {
    }

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
        $formOptions['block_prefix'] = $filterName;
        $formOptions['required'] = false;
        $formOptions['multiple'] = $options['multiple'];
        $formOptions['expanded'] = $options['expanded'];
        $formOptions['choices'] = $this->getChoices($aggregationResult);

        $formOptions['choice_value'] = function (?ValueObject $entry): string {
            return $entry ? $this->getChoiceValue($entry) : '';
        };
        $formOptions['choice_label'] = function (?ValueObject $entry): string {
            return $entry ? $this->getChoiceLabel($entry) : '';
        };
        $formOptions['choice_attr'] = function (?ValueObject $entry): array {
            return $entry ? $this->getChoiceAttributes($entry) : [];
        };
        $formBuilder->add($filterName, ChoiceType::class, $formOptions);
    }

    /**
     * @param \Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResultEntry $entry
     */
    protected function getChoiceValue(ValueObject $entry): string
    {
        return $entry->getKey();
    }

    /**
     * @param \Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResultEntry $entry
     */
    protected function getChoiceLabel(ValueObject $entry): string
    {
        return $entry->getName();
    }

    /**
     * @param \Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResultEntry $entry
     */
    protected function getChoiceAttributes(ValueObject $entry): array
    {
        return [];
    }

    /**
     * @param \Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResult $aggregationResult
     */
    protected function getChoices(?AggregationResult $aggregationResult = null): array
    {
        $choices = [];
        if ($aggregationResult) {
            foreach ($aggregationResult->getEntries() as $entry) {
                $choices[] = $entry;
            }
        }
        return $choices;
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

        // only used for static
        $optionsResolver->define('choices')
            ->default(null)
            ->allowedTypes('null', 'string[]');
    }

    public function getFakeFormType(): array
    {
        return [
            'type' => ChoiceType::class,
            'options' => [
                'choices' => array_flip($this->fakerGenerator->words()),
                'expanded' => false,
                'multiple' => false,
            ],
        ];
    }
}
