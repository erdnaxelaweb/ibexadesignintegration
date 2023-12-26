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

use DateTime;
use Exception;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\CustomField;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateFilterHandler extends AbstractFilterHandler
{
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
        $formOptions['widget'] = $options['widget'];
        $formBuilder->add($filterName, DateType::class, $formOptions);
    }

    public function getCriterion(string $filterName, $value, array $options = []): Criterion
    {
        $options = $this->resolveOptions($options);
        $operator = $options['operator'];
        return new CustomField($options['field'], $operator, $this->mapDate($value));
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
    }

    protected function mapDate($value): string
    {
        if (is_numeric($value)) {
            $date = new DateTime("@{$value}");
        } else {
            try {
                $date = new DateTime($value);
            } catch (Exception $e) {
                throw new InvalidArgumentException('Invalid date provided: ' . $value);
            }
        }

        return $date->format('Y-m-d\\TH:i:s\\Z');
    }
}
