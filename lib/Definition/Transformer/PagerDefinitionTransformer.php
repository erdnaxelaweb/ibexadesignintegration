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

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\Factory\SearchTypeFactoryInterface;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionInterface;
use ErdnaxelaWeb\StaticFakeDesign\Definition\Transformer\PagerDefinitionTransformer as NativePagerDefinitionTransformer;
use ErdnaxelaWeb\StaticFakeDesign\Definition\Transformer\PagerFilterDefinitionTransformer;
use ErdnaxelaWeb\StaticFakeDesign\Definition\Transformer\PagerSortDefinitionTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\VarExporter\Instantiator;

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

    public function fromHash(array $hash): PagerDefinition
    {
        return $this->lazyFromHash(Instantiator::instantiate(PagerDefinition::class, [
            'identifier' => $hash['identifier'],
        ]), $hash['hash']);
    }

    /**
     * @param PagerDefinition $definition
     */
    public function toHash(DefinitionInterface $definition): array
    {
        $hash = parent::toHash($definition);
        $hash['disablePagination'] = $definition->isPaginationDisabled();
        return $hash;
    }

    public function configureOptions(OptionsResolver $optionsResolver, array $options): void
    {
        parent::configureOptions($optionsResolver, $options);

        $optionsResolver->remove('searchType');
        $optionsResolver->define('searchType')
            ->default('location')
            ->allowedTypes('string')
            ->allowedValues(...$this->availableSearchTypes);

        $optionsResolver->define('disablePagination')
            ->default(false)
            ->allowedTypes('bool');
    }
}
