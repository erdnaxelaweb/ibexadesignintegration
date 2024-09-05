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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
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
        $formOptions['block_prefix'] = "filter_$filterName";
        $formOptions['required'] = false;
        $formOptions['multiple'] = $options['multiple'];
        $formOptions['expanded'] = $options['expanded'];
        $choices = $this->getChoices($aggregationResult);

        if (isset($options['nameSort']) && $options['nameSort']) {
            usort($choices, function($a, $b) {
                return strcasecmp($a->getName(), $b->getName());
            });
        }

        $formOptions['choices'] = $choices;

        $formOptions['choice_value'] = function ($entry): ?string {
            return $entry instanceof ValueObject ? $this->getChoiceValue($entry) : $entry;
        };
        $formOptions['choice_label'] = function ($entry): ?string {
            return $entry instanceof ValueObject ? $this->getChoiceLabel($entry) : $entry;
        };
        $formOptions['choice_attr'] = function ($entry): array {
            return $entry instanceof ValueObject ? $this->getChoiceAttributes($entry) : [];
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
        return $this->getValueLabel($entry->getName());
    }

    protected function getValueLabel(string $value): string
    {
        return $value;
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
        return new FilterTag($filterName, $criterion);
    }

    public function getAggregation(string $filterName, array $options = []): ?Aggregation
    {
        $options = $this->resolveOptions($options);
        $aggregation = new RawTermAggregation($filterName, $options['field'], [$filterName]);
        $aggregation->setLimit($options['limit']);
        return $aggregation;
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
        $optionsResolver->define('limit')
            ->default(10)
            ->allowedTypes('integer');
        $optionsResolver->define('nameSort')
            ->default(false)
            ->allowedTypes('bool');

        // only used for static
        $optionsResolver->define('choices')
            ->default(null)
            ->allowedTypes('null', 'array')
            ->normalize(function (Options $options, $rawChoices) {
                $optionsResolver = new OptionsResolver();
                $optionsResolver->define('label')
                    ->required()
                    ->allowedTypes('string');
                $optionsResolver->define('value')
                    ->required();
                $optionsResolver->define('attr')
                    ->default([])
                    ->allowedTypes('array');

                if (empty($rawChoices)) {
                    return $rawChoices;
                }
                $choices = [];
                foreach ($rawChoices as $rawChoice) {
                    if ($rawChoice instanceof \stdClass) {
                        $choice = $rawChoice;
                    } else {
                        $choiceOptions = $optionsResolver->resolve($rawChoice);

                        $choice = new \stdClass();
                        $choice->label = $choiceOptions['label'];
                        $choice->value = $choiceOptions['value'];
                        $choice->attr = $choiceOptions['attr'];
                    }

                    $choices[] = $choice;
                }

                return $choices;
            });
    }

    public function getFakeFormType(): array
    {
        $words = $this->fakerGenerator->words();
        return [
            'type' => ChoiceType::class,
            'options' => [
                'choices' => array_map(function ($label, $value) {
                    $choice = new \stdClass();
                    $choice->label = $label;
                    $choice->value = $value;
                    $choice->attr = [];
                    return $choice;
                }, $words, range(0, count($words))),
                'expanded' => false,
                'multiple' => false,
                'choice_value' => function ($entry): ?string {
                    return is_object($entry) ? $entry->value : $entry;
                },
                'choice_label' => function ($entry): ?string {
                    return is_object($entry) ? $entry->label : $entry;
                },
                'choice_attr' => function ($entry): array {
                    return is_object($entry) ? $entry->attr : [];
                },
            ],
        ];
    }

    public function getValuesLabels(array $activeValues, FormInterface $formBuilder): array
    {
        /** @var \Symfony\Component\Form\ChoiceList\ArrayChoiceList $choices */
        $choices = $formBuilder->getConfig()
            ->getAttribute('choice_list')
            ->getChoices();

        $labels = array_combine($activeValues, array_map(function ($activeValue) use ($choices) {
            return isset($choices[$activeValue]) ? $this->getChoiceLabel($choices[$activeValue]) : $this->getValueLabel(
                $activeValue
            );
        }, $activeValues));

        return $labels;
    }
}
