<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use InvalidArgumentException;

abstract class AbstractFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __invoke(
        AbstractContent        $content,
        string                 $fieldIdentifier,
        ?FieldDefinition        $ibexaFieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): mixed {
        if (!$this->support($ibexaFieldDefinition?->getFieldTypeIdentifier())) {
            throw new InvalidArgumentException(
                sprintf(
                    'The field type "%s" is not supported by the transformer "%s".',
                    $ibexaFieldDefinition->getFieldTypeIdentifier(),
                    static::class
                )
            );
        }

        return $this->transformFieldValue(
            $content,
            $fieldIdentifier,
            $ibexaFieldDefinition,
            $contentFieldDefinition
        );
    }

    /**
     * @return mixed
     */
    abstract protected function transformFieldValue(
        AbstractContent        $content,
        string                 $fieldIdentifier,
        ?FieldDefinition       $ibexaFieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    );
}
