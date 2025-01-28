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

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\Choice\FilterChoiceInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\Choice\TaxonomyFilterChoice;
use ErdnaxelaWeb\StaticFakeDesign\Fake\FakerGenerator;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Taxonomy\Service\TaxonomyServiceInterface;
use Ibexa\Taxonomy\Exception\TaxonomyEntryNotFoundException;
use Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResultEntry;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaxonomyFieldFilterHandler extends CustomFieldFilterHandler
{
    public function __construct(
        protected TaxonomyServiceInterface $taxonomyService,
        FakerGenerator $fakerGenerator
    ) {
        parent::__construct($fakerGenerator);
    }

    protected function getValueLabel(string $value): string
    {
        try {
            return $this->taxonomyService->loadEntryById($value)
                ->getName();
        } catch (TaxonomyEntryNotFoundException $entryNotFoundException) {
            return $value;
        }
    }

    protected function getFormOptions(
        FormBuilderInterface $formBuilder,
        string               $filterName,
        ?AggregationResult   $aggregationResult,
        array                $options
    ): array {
        $formOptions = parent::getFormOptions($formBuilder, $filterName, $aggregationResult, $options);
        if ($options['group_by_parent']) {
            $formOptions['group_by'] = function (FilterChoiceInterface $choice, $key, $value) {
                return $choice instanceof TaxonomyFilterChoice ? $choice->getParent() : null;
            };
        }

        return $formOptions;
    }

    protected function getChoices(?AggregationResult $aggregationResult, string $filterName, array $options): array
    {
        $choices = parent::getChoices($aggregationResult, $filterName, $options);

        if ($options['group_by_parent'] && $options['sort'] === 'label') {
            usort(
                $choices,
                static function (FilterChoiceInterface $choice1, FilterChoiceInterface $choice2) use ($options) {
                    $parent1 = $choice1 instanceof TaxonomyFilterChoice ? $choice1->getParent() : null;
                    $parent2 = $choice2 instanceof TaxonomyFilterChoice ? $choice2->getParent() : null;
                    if ($options['sort_direction'] === 'asc') {
                        return strnatcasecmp($parent1, $parent2);
                    } else {
                        return strnatcasecmp($parent2, $parent1);
                    }
                }
            );
        }
        return $choices;
    }

    protected function buildChoiceFromAggregationResultEntry(
        RawTermAggregationResultEntry $entry,
        array $options
    ): FilterChoiceInterface {
        try {
            $taxonomyEntry = $this->taxonomyService->loadEntryById((int) $entry->getKey());
            return new TaxonomyFilterChoice(
                $taxonomyEntry,
                $entry->getKey(),
                $entry->getCount(),
                [
                    'class' => $taxonomyEntry->getIdentifier(),
                ],
                $options['choice_label_format']
            );
        } catch (TaxonomyEntryNotFoundException $entryNotFoundException) {
            return parent::buildChoiceFromAggregationResultEntry($entry, $options);
        }
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->define('group_by_parent')
            ->default(false)
            ->allowedTypes('bool');
    }
}
