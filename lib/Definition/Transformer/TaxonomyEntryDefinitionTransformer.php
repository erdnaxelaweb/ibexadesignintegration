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

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\TaxonomyEntryDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Definition\Transformer\TaxonomyEntryDefinitionTransformer as NativeTaxonomyEntryDefinitionTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\VarExporter\Instantiator;

class TaxonomyEntryDefinitionTransformer extends NativeTaxonomyEntryDefinitionTransformer
{
    public function configureOptions(OptionsResolver $optionsResolver, array $options): void
    {
        parent::configureOptions($optionsResolver, $options);

        $optionsResolver->define('name')
            ->required()
            ->allowedTypes('string', 'array');

        $optionsResolver->define('description')
            ->default('')
            ->allowedTypes('string', 'array');
    }

    public function fromHash(array $hash): TaxonomyEntryDefinition
    {
        return $this->lazyFromHash(Instantiator::instantiate(TaxonomyEntryDefinition::class, [
            "identifier" => $hash['identifier'],
        ]), $hash['hash']);
    }
}
