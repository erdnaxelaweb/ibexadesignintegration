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

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionInterface;
use ErdnaxelaWeb\StaticFakeDesign\Definition\Transformer\ContentFieldDefinitionTransformer as NativeContentFieldDefinitionTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\VarExporter\Instantiator;

class ContentFieldDefinitionTransformer extends NativeContentFieldDefinitionTransformer
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

        $optionsResolver->define('searchable')
            ->default(false)
            ->allowedTypes('bool');

        $optionsResolver->define('infoCollector')
            ->default(false)
            ->allowedTypes('bool');

        $optionsResolver->define('translatable')
            ->default(true)
            ->allowedTypes('bool');

        $optionsResolver->define('category')
            ->default('content')
            ->allowedTypes('string');
    }

    public function fromHash(array $hash): ContentFieldDefinition
    {
        return $this->lazyFromHash(Instantiator::instantiate(
            ContentFieldDefinition::class,
            [
                'identifier' => $hash['identifier'],
            ]
        ), $hash['hash']);
    }

    /**
     * @param ContentFieldDefinition $definition
     */
    public function toHash(DefinitionInterface $definition): array
    {
        $hash = parent::toHash($definition);
        $hash['hash'] += [
            "name" => $definition->getName(),
            "description" => $definition->getDescription(),
            "searchable" => $definition->isSearchable(),
            "infoCollector" => $definition->isInfoCollector(),
            "translatable" => $definition->isTranslatable(),
            "category" => $definition->getCategory(),
        ];
        return $hash;
    }
}
