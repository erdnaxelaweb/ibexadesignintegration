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

class FieldValueTransformer
{
    /**
     * @var FieldValueTransformerInterface[]
     */
    protected array $fieldValueTransformers = [];

    /**
     * @param iterable<FieldValueTransformerInterface> $transformers
     */
    public function __construct(iterable $transformers)
    {
        foreach ($transformers as $type => $fieldValueTransformer) {
            $this->fieldValueTransformers[$type] = $fieldValueTransformer;
        }
    }

    public function transform(
        AbstractContent $content,
        string $fieldIdentifier,
        ContentFieldDefinition $contentFieldDefinition
    ): mixed {
        $fieldDefinition = $content->getContentType()
            ->getFieldDefinition($fieldIdentifier);
        if ($fieldDefinition) {
            $fieldValueTransformer = $this->fieldValueTransformers[$fieldDefinition->fieldTypeIdentifier];
            return $fieldValueTransformer->transformFieldValue(
                $content,
                $fieldIdentifier,
                $fieldDefinition,
                $contentFieldDefinition
            );
        }
        return null;
    }
}
