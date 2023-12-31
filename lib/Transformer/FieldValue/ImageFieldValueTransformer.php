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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue;

use ErdnaxelaWeb\IbexaDesignIntegration\Helper\ImageGenerator;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

class ImageFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected ImageGenerator $imageGenerator,
        protected ContentService $contentService,
    ) {
    }

    public function transformFieldValue(
        Content         $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        array $fieldConfiguration
    ) {
        return function (string $variationName) use ($content, $fieldIdentifier, $fieldDefinition) {
            return $this->imageGenerator->generateImage($content, $fieldIdentifier, $variationName);
        };
    }
}
