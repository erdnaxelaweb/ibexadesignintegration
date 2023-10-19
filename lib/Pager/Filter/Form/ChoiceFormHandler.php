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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Form;

use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class ChoiceFormHandler implements FilterFormHandlerInterface
{
    public function addForm(
        FormBuilderInterface $formBuilder,
        string               $filterName,
        string               $field,
        AggregationResult    $aggregationResult
    ): void {
        $options = $this->getFormOptions();
        $options['label'] = sprintf('searchform.%s', $filterName);
        $options['required'] = false;
        $options['choices'] = [];
        /** @var \Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResultEntry $value */
        foreach ($aggregationResult->getEntries() as $value) {
            $options['choices'][$value->getKey()] = $value->getKey();
        }
        $formBuilder->add($filterName, ChoiceType::class, $options);
    }

    abstract protected function getFormOptions(): array;
}
