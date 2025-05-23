<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\DependencyInjection\Compiler;

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttributeValueTransformer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class BlockAttributeValueTransformerPass implements CompilerPassInterface
{
    public const BLOCK_ATTRIBUTE_VALUE_TRANSFORMER_TAG = 'erdnaxelaweb.ibexa_design_integration.transformer.block_attribute_value';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(BlockAttributeValueTransformer::class)) {
            return;
        }
        $transformerDefinition = $container->getDefinition(BlockAttributeValueTransformer::class);
        $taggedServices = $container->findTaggedServiceIds(self::BLOCK_ATTRIBUTE_VALUE_TRANSFORMER_TAG);

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                $transformerDefinition->addMethodCall(
                    'registerTransformer',
                    [
                        $tag['type'],
                        new Reference($id),
                    ]
                );
            }
        }
    }
}
