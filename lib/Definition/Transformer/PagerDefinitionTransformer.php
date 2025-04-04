<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Definition\Transformer;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\Factory\SearchTypeFactoryInterface;
use ErdnaxelaWeb\StaticFakeDesign\Definition\Transformer\PagerDefinitionTransformer as NativePagerDefinitionTransformer;
use ErdnaxelaWeb\StaticFakeDesign\Definition\Transformer\PagerFilterDefinitionTransformer;
use ErdnaxelaWeb\StaticFakeDesign\Definition\Transformer\PagerSortDefinitionTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PagerDefinitionTransformer extends NativePagerDefinitionTransformer
{
    /**
     * @var array<SearchTypeFactoryInterface>
     */
    protected array $availableSearchTypes;

    /**
     * @param iterable<SearchTypeFactoryInterface>                                                   $searchTypeFactories
     */
    public function __construct(
        PagerFilterDefinitionTransformer $pagerFilterDefinitionTransformer,
        PagerSortDefinitionTransformer $pagerSortDefinitionTransformer,
        iterable $searchTypeFactories,
    ) {
        parent::__construct(
            $pagerFilterDefinitionTransformer,
            $pagerSortDefinitionTransformer
        );
        $this->availableSearchTypes = [];
        foreach ($searchTypeFactories as $type => $searchTypeFactory) {
            $this->availableSearchTypes[] = $type;
        }
    }

    public function configureOptions(OptionsResolver $optionsResolver, array $options): void
    {
        parent::configureOptions($optionsResolver, $options);

        $optionsResolver->define('searchType')
            ->default('location')
            ->allowedTypes('string')
            ->allowedValues(...$this->availableSearchTypes);
    }
}
