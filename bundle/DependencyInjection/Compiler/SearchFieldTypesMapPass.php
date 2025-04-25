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

use ErdnaxelaWeb\IbexaDesignIntegration\Document\SearchFieldResolver;
use Ibexa\Contracts\Core\Search\FieldType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SearchFieldTypesMapPass implements CompilerPassInterface
{
    public const TAG = 'ibexa.search.common.field_value.mapper';

    public function process(ContainerBuilder $container): void
    {
        $fieldTypeGuesserDefinition = $container->getDefinition(SearchFieldResolver::class);
        $taggedServicesIds = $container->findTaggedServiceIds(self::TAG);
        $map = [];
        foreach ($taggedServicesIds as $id => $tags) {
            foreach ($tags as $tagAttributes) {
                /** @var class-string<FieldType> $fieldTypeClass */
                $fieldTypeClass = $tagAttributes['maps'];
                $fieldType = new $fieldTypeClass();
                $map[$fieldType->getType()] = $fieldTypeClass;
            }
        }
        $fieldTypeGuesserDefinition->replaceArgument('$searchFieldTypesMap', $map);
    }
}
