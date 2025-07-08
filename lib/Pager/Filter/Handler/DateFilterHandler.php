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

use DateTime;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionOptions;
use Exception;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\CustomField;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use IntlDateFormatter;
use InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateFilterHandler extends AbstractFilterHandler
{
    public function addForm(
        FormBuilderInterface $formBuilder,
        string $filterName,
        DefinitionOptions $options,
        AggregationResultCollection $aggregationResultCollection
    ): void {
        $formOptions['label'] = sprintf('searchform.%s', $filterName);
        $formOptions['block_prefix'] = "filter_$filterName";
        $formOptions['required'] = false;
        $formOptions['widget'] = $options['widget'];
        $formOptions['format'] = $options['format'];
        $formOptions['input_format'] = $options['input_format'];
        $formOptions['html5'] = $options['html5'];
        $formBuilder->add($filterName, DateType::class, $formOptions);
    }

    public function getCriterion(string $filterName, mixed $value, DefinitionOptions $options, array $searchData): ?Criterion
    {
        $operator = $options['operator'];
        return new CustomField($options['field'], $operator, $this->mapDate($value, $options['input_format']));
    }

    public function getFakeFormType(): array
    {
        return [
            'type' => DateType::class,
            'options' => [
                'widget' => 'single_text',
            ],
        ];
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->define('field')
            ->required()
            ->allowedTypes('string');
        $optionsResolver->define('operator')
            ->default(Criterion\Operator::EQ)
            ->allowedTypes('string');
        $optionsResolver->define('widget')
            ->default('single_text')
            ->allowedTypes('string');
        $optionsResolver->define('format')
            ->default(IntlDateFormatter::MEDIUM)
            ->allowedTypes('string', 'int');
        $optionsResolver->define('input_format')
            ->default('Y-m-d')
            ->allowedTypes('string');
        $optionsResolver->define('html5')
            ->default(true)
            ->allowedTypes('boolean');
    }

    protected function mapDate(mixed $value, string $inputFormat): string
    {
        if (is_numeric($value)) {
            $date = new DateTime("@{$value}");
        } else {
            try {
                $date = DateTime::createFromFormat($inputFormat, $value);
            } catch (Exception $e) {
                throw new InvalidArgumentException('Invalid date provided: ' . $value);
            }
        }

        return $date->format('Y-m-d\\TH:i:s\\Z');
    }
}
