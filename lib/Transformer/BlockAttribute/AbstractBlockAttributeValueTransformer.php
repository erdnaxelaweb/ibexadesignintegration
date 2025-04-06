<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute;

use ErdnaxelaWeb\StaticFakeDesign\Definition\BlockAttributeDefinition;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;
use InvalidArgumentException;

abstract class AbstractBlockAttributeValueTransformer implements BlockAttributeValueTransformerInterface
{
    public function __invoke(
        BlockValue               $blockValue,
        string                   $attributeIdentifier,
        BlockDefinition          $ibexaBlockDefinition,
        BlockAttributeDefinition $attributeDefinition
    ): mixed {
        $attribute = $ibexaBlockDefinition->getAttribute($attributeIdentifier);
        if (!$this->support($attribute->getType())) {
            throw new InvalidArgumentException(
                sprintf(
                    'The attribute type "%s" is not supported by the transformer "%s".',
                    $attribute->getType(),
                    static::class
                )
            );
        }

        return $this->transformAttributeValue(
            $blockValue,
            $attributeIdentifier,
            $ibexaBlockDefinition,
            $attributeDefinition
        );
    }

    /**
     * @return mixed
     */
    abstract protected function transformAttributeValue(
        BlockValue               $blockValue,
        string                   $attributeIdentifier,
        BlockDefinition          $ibexaBlockDefinition,
        BlockAttributeDefinition $attributeDefinition
    );
}
