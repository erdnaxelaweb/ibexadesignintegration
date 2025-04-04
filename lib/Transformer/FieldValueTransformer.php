<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
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
     * @var \ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue\FieldValueTransformerInterface[]
     */
    protected array $fieldValueTransformers = [];

    public function __construct(
        iterable $transformers
    ) {
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
