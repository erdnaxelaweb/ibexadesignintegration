<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\Choice\FilterChoice;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\Choice\FilterChoiceInterface;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionOptions;
use ErdnaxelaWeb\StaticFakeDesign\Fake\FakerGenerator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\CustomField;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Novactive\EzSolrSearchExtra\Query\Aggregation\RawTermAggregation;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\FilterTag;
use Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResult;
use Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResultEntry;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomFieldFilterHandler extends AbstractFilterHandler implements NestableFilterHandlerInterface
{
    public function __construct(
        protected FakerGenerator $fakerGenerator
    ) {
    }

    /**
     * @param RawTermAggregationResult $aggregationResult
     */
    public function addForm(
        FormBuilderInterface $formBuilder,
        string $filterName,
        DefinitionOptions $options,
        ?AggregationResult $aggregationResult = null,
    ): void {
        $formBuilder->add(
            $filterName,
            ChoiceType::class,
            $this->getFormOptions($formBuilder, $filterName, $aggregationResult, $options)
        );
    }

    public function getCriterion(string $filterName, mixed $value, DefinitionOptions $options): Criterion
    {
        $operator = is_array($value) ? Operator::IN : Operator::EQ;
        $criterion = new CustomField($options['field'], $operator, $value);
        return new FilterTag($filterName, $criterion);
    }

    public function getAggregation(string $filterName, DefinitionOptions $options): ?Aggregation
    {
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
        $optionsResolver->define('choice_label_format')
            ->default('%name% (%count%)')
            ->allowedTypes('string')
            ->info('Available placeholders : %name% / %value% / %count%');
        $optionsResolver->define('sort')
            ->default('count')
            ->allowedTypes('string')
            ->allowedValues('count', 'label');
        $optionsResolver->define('sort_direction')
            ->default('asc')
            ->allowedTypes('string')
            ->allowedValues('asc', 'desc');
        $optionsResolver->define('is_nested')
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
                }, $words, range(1, count($words))),
                'expanded' => false,
                'multiple' => false,
                'choice_value' => function ($entry): ?int {
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

    public function getValuesLabels($activeValues, FormInterface $formBuilder): mixed
    {
        /** @var \Symfony\Component\Form\ChoiceList\ArrayChoiceList $choices */
        $choices = $formBuilder->getConfig()
            ->getAttribute('choice_list')
            ->getChoices();

        $activeValues = (array) $activeValues;
        return array_combine($activeValues, array_map(function ($activeValue) use ($choices) {
            $choice = $choices[$activeValue] ?? $this->getValueLabel($activeValue);
            return $choice instanceof FilterChoiceInterface ? $choice->getLabel() : $choice;
        }, $activeValues));
    }

    /**
     * @param RawTermAggregationResult|null $aggregationResult
     *
     * @return array<string, mixed>
     */
    protected function getFormOptions(
        FormBuilderInterface $formBuilder,
        string $filterName,
        ?AggregationResult $aggregationResult,
        DefinitionOptions $options
    ): array {
        $formOptions['label'] = sprintf('searchform.%s', $filterName);
        $formOptions['block_prefix'] = "filter_$filterName";
        $formOptions['required'] = false;
        $formOptions['multiple'] = $options['multiple'];
        $formOptions['expanded'] = $options['expanded'];

        $formOptions['choice_loader'] = new CallbackChoiceLoader(function () use (
            $aggregationResult,
            $filterName,
            $options
        ): array {
            if ($options['is_nested']) {
                $choices = [];
                foreach ($aggregationResult->getEntries() as $entry) {
                    $nestedAggregationResult = $entry->getNestedResults()[$filterName] ?? [];
                    if (!$nestedAggregationResult instanceof RawTermAggregationResult) {
                        continue;
                    }
                    $choices[$entry->getName()] = $this->getChoices($nestedAggregationResult, $filterName, $options);
                }
            } else {
                $choices = $this->getChoices($aggregationResult, $filterName, $options);
            }
            return $choices;
        });

        $formOptions['choice_value'] = 'value';
        $formOptions['choice_label'] = 'label';
        $formOptions['choice_attr'] = 'attr';
        return $formOptions;
    }

    protected function getValueLabel(mixed $value): string
    {
        return $value;
    }

    /**
     * @return FilterChoiceInterface[]
     */
    protected function buildChoicesFromAggregationResult(
        ?AggregationResult $aggregationResult,
        string $filterName,
        DefinitionOptions $options
    ): array {
        $choices = [];
        if ($aggregationResult && method_exists($aggregationResult, 'getEntries')) {
            foreach ($aggregationResult->getEntries() as $entry) {
                $choices[] = $this->buildChoiceFromAggregationResultEntry($entry, $options);
            }
        }
        return $choices;
    }

    /**
     * @return FilterChoiceInterface[]
     */
    protected function getChoices(
        ?AggregationResult $aggregationResult,
        string $filterName,
        DefinitionOptions $options
    ): array {
        $choices = $this->buildChoicesFromAggregationResult($aggregationResult, $filterName, $options);
        switch ($options['sort']) {
            case 'label':
                usort(
                    $choices,
                    static function (FilterChoiceInterface $choice1, FilterChoiceInterface $choice2) use ($options) {
                        if ($options['sort_direction'] === 'asc') {
                            return strnatcasecmp($choice1->getLabel(), $choice2->getLabel());
                        } else {
                            return strnatcasecmp($choice2->getLabel(), $choice1->getLabel());
                        }
                    }
                );
                break;
            default:
                break;
        }
        return $choices;
    }

    protected function buildChoiceFromAggregationResultEntry(
        RawTermAggregationResultEntry $entry,
        DefinitionOptions $options
    ): FilterChoiceInterface {
        return new FilterChoice(
            $entry->getName(),
            $entry->getKey(),
            $entry->getCount(),
            [],
            $options['choice_label_format']
        );
    }
}
