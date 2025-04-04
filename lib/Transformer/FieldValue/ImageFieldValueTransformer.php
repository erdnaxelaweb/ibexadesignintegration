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

use Closure;
use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Helper\ImageGenerator;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use ErdnaxelaWeb\StaticFakeDesign\Value\Image;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Helper\FieldHelper;

class ImageFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected ImageGenerator $imageGenerator,
        protected ContentService $contentService,
        protected FieldHelper $fieldHelper,
    ) {
    }

    public function transformFieldValue(
        AbstractContent $content,
        string $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): Closure {
        return function (string $variationName) use ($content, $fieldIdentifier): ?Image {
            if ($this->fieldHelper->isFieldEmpty($content, $fieldIdentifier)) {
                return null;
            }

            return $this->imageGenerator->generateImage($content, $fieldIdentifier, $variationName);
        };
    }
}
