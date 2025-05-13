<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue\FieldValueTransformerInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use InvalidArgumentException;

class FieldValueTransformer
{
    /**
     * @var array<string, FieldValueTransformerInterface[]>
     */
    protected array $fieldValueTransformers = [];

    public function registerTransformer(string $type, FieldValueTransformerInterface $fieldValueTransformer): void
    {
        if (!array_key_exists($type, $this->fieldValueTransformers)) {
            $this->fieldValueTransformers[$type] = [];
        }
        $this->fieldValueTransformers[$type][] = $fieldValueTransformer;
    }

    public function getTransformer(string $contentFieldType, ?string $ibexaFieldTypeIdentifier): FieldValueTransformerInterface
    {
        if (!array_key_exists($contentFieldType, $this->fieldValueTransformers)) {
            throw new InvalidArgumentException(sprintf('No transformer found for field type "%s".', $contentFieldType));
        }

        $transformers = $this->fieldValueTransformers[$contentFieldType];
        foreach ($transformers as $transformer) {
            if ($transformer->support($ibexaFieldTypeIdentifier)) {
                return $transformer;
            }
        }
        throw new InvalidArgumentException(sprintf('No transformer found for ibexa field type "%s".', $ibexaFieldTypeIdentifier));
    }

    public function transform(
        AbstractContent $content,
        string $fieldIdentifier,
        ContentFieldDefinition $contentFieldDefinition
    ): mixed {
        $ibexaFieldDefinition = $content->getContentType()->getFieldDefinition($fieldIdentifier);
        $fieldValueTransformer = $this->getTransformer(
            $contentFieldDefinition->getType(),
            $ibexaFieldDefinition?->getFieldTypeIdentifier()
        );
        return ($fieldValueTransformer)(
            $content,
            $fieldIdentifier,
            $ibexaFieldDefinition,
            $contentFieldDefinition
        );
    }
}
