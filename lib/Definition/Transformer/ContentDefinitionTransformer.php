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

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionInterface;
use ErdnaxelaWeb\StaticFakeDesign\Definition\Transformer\ContentDefinitionTransformer as NativeContentDefinitionTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\VarExporter\Instantiator;

class ContentDefinitionTransformer extends NativeContentDefinitionTransformer
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

        $optionsResolver->define('nameSchema')
            ->default('')
            ->allowedTypes('string');

        $optionsResolver->define('urlAliasSchema')
            ->default('')
            ->allowedTypes('string');

        $optionsResolver->define('container')
            ->default(false)
            ->allowedTypes('bool');

        $optionsResolver->define('defaultAlwaysAvailable')
            ->default(false)
            ->allowedTypes('bool');

        $optionsResolver->define('defaultSortField')
            ->default('published')
            ->allowedTypes('string')
            ->allowedValues(
                'path',
                'published',
                'modified',
                'section',
                'depth',
                'class_identifier',
                'class_name',
                'priority',
                'name'
            );

        $optionsResolver->define('defaultSortOrder')
            ->default('desc')
            ->allowedTypes('string')
            ->allowedValues('desc', 'asc');
    }

    public function fromHash(array $hash): ContentDefinition
    {
        return $this->lazyFromHash(Instantiator::instantiate(ContentDefinition::class, [
            'identifier' => $hash['identifier'],
        ]), $hash['hash']);
    }

    /**
     * @param ContentDefinition $definition
     */
    public function toHash(DefinitionInterface $definition): array
    {
        $hash = parent::toHash($definition);
        $hash['hash'] += [
            "name" => $definition->getName(),
            "description" => $definition->getDescription(),
            "nameSchema" => $definition->getNameSchema(),
            "urlAliasSchema" => $definition->getUrlAliasSchema(),
            "container" => $definition->isContainer(),
            "defaultAlwaysAvailable" => $definition->isDefaultAlwaysAvailable(),
            "defaultSortField" => $definition->getDefaultSortField(),
            "defaultSortOrder" => $definition->getDefaultSortOrder(),
        ];
        return $hash;
    }
}
