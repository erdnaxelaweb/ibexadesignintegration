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

class GenericFieldValueTransformer extends AbstractFieldValueTransformer
{
    /**
     * @param string[]  $supportedTypes
     */
    public function __construct(
        protected string $propertyName = "value",
        protected array $supportedTypes = []
    ) {
    }

    public function support(?string $ibexaFieldTypeIdentifier): bool
    {
        return in_array($ibexaFieldTypeIdentifier, $this->supportedTypes, true);
    }

    protected function transformFieldValue(
        AbstractContent        $content,
        string                 $fieldIdentifier,
        ?FieldDefinition       $ibexaFieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): mixed {
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        return $fieldValue->{$this->propertyName};
    }
}
