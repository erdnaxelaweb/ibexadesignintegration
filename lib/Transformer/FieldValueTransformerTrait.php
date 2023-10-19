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

use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaContent;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

trait FieldValueTransformerTrait
{
    /**
     * @var \ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue\FieldValueTransformerInterface[]
     */
    protected array $fieldValueTransformers = [];

    protected function transformFieldValue(
        IbexaContent $ibexaContent,
        ContentType $contentType,
        string $fieldIdentifier
    ) {
        $fieldDefinition = $contentType->getFieldDefinition($fieldIdentifier);
        $fieldValue = null;
        if ($fieldDefinition) {
            $fieldValueTransformer = $this->fieldValueTransformers[$fieldDefinition->fieldTypeIdentifier];
            $fieldValue = $fieldValueTransformer->transformFieldValue(
                $ibexaContent,
                $fieldIdentifier,
                $fieldDefinition
            );
        }
        return $fieldValue;
    }
}
