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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Helper;

use ErdnaxelaWeb\StaticFakeDesign\Configuration\ImageConfiguration;
use ErdnaxelaWeb\StaticFakeDesign\Value\Image;
use ErdnaxelaWeb\StaticFakeDesign\Value\ImageFocusPoint;
use ErdnaxelaWeb\StaticFakeDesign\Value\ImageSource;
use Ibexa\Bundle\Core\Imagine\IORepositoryResolver;
use Ibexa\Contracts\Core\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidVariationException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Variation\Values\ImageVariation;
use Ibexa\Contracts\Core\Variation\Values\Variation;
use Ibexa\Contracts\Core\Variation\VariationHandler;
use Ibexa\Core\FieldType\Image\Value as ImageValue;
use Ibexa\Core\FieldType\ImageAsset\Value as ImageAssetValue;
use Ibexa\Core\MVC\Exception\SourceImageNotFoundException;
use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Novactive\EzEnhancedImageAsset\Values\FocusedVariation;
use Psr\Log\LoggerInterface;
use ReflectionException;

class ImageGenerator
{
    public function __construct(
        protected VariationHandler   $imageVariationService,
        protected ImageConfiguration $imageConfiguration,
        protected ContentService     $contentService,
        protected LoggerInterface    $imageVariationLogger
    ) {
    }

    protected function getImageVariationIfExist(Field $field, VersionInfo $versionInfo, $variationName): ?Variation
    {
        try {
            return $this->imageVariationService->getVariation($field, $versionInfo, $variationName);
        } catch (SourceImageNotFoundException $e) {
            if (isset($this->imageVariationLogger)) {
                $this->imageVariationLogger->error(
                    "Couldn't create variation '{$variationName}' for image with id {$field->value->id} 
                        because source image can't be found"
                );
            }
            throw $e;
        } catch (InvalidArgumentException|InvalidVariationException|ReflectionException $e) {
            if (isset($this->imageVariationLogger)) {
                $this->imageVariationLogger->error(
                    "Couldn't create variation '{$variationName}' for image with id {$field->value->id} 
                        because an image could not be created from the given input"
                );
            }
            throw $e;
        }

        return null;
    }

    public function generateImage(IbexaContent $content, string $fieldIdentifier, string $variationName)
    {
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        if ($fieldValue instanceof ImageValue) {
            return $this->getImage($content, $content->getField($fieldIdentifier), $variationName);
        }
        if ($fieldValue instanceof ImageAssetValue && $fieldValue->destinationContentId) {
            $relatedContent = $this->contentService->loadContent($fieldValue->destinationContentId);
            return $this->generateImage($relatedContent, 'image', $variationName);
        }
    }

    protected function getImage(IbexaContent $content, Field $field, string $variationName = 'original'): ?Image
    {
        /** @var ImageValue $imageFieldValue */
        $imageFieldValue = $field->value;
        $sources = $this->getImageSources($field, $content->getVersionInfo(), $variationName);

        return new Image(
            $imageFieldValue->alternativeText,
            $content->getFieldValue('caption'),
            $content->getFieldValue('credits'),
            $sources,
        );
    }

    protected function getImageSources(Field $field, VersionInfo $versionInfo, string $variationName): array
    {
        if ($variationName === IORepositoryResolver::VARIATION_ORIGINAL) {
            try {
                $variation = $this->getImageVariationIfExist($field, $versionInfo, $variationName);
            } catch (SourceImageNotFoundException $e) {
                return [];
            }
            return [$this->getImageVariationSource([$variation->uri], '', $variation, $variationName)];
        } else {
            $variationConfig = $this->imageConfiguration->getVariationConfig($variationName);
        }

        $sources = [];
        foreach ($variationConfig as $sourceReqs) {
            $sourceVariationName = "{$variationName}_{$sourceReqs['suffix']}";
            $typeVariationNames = [
                '' => $sourceVariationName,
                ' 2x' => $sourceVariationName . '_retina',
            ];
            $uris = [];
            $baseVariation = null;

            foreach ($typeVariationNames as $variationType => $typeVariationName) {
                try {
                    $variation = $this->getImageVariationIfExist($field, $versionInfo, $typeVariationName);
                    $uris[] = $variation->uri . $variationType;
                    if (! $baseVariation) {
                        $baseVariation = $variation;
                    }
                } catch (NonExistingFilterException|SourceImageNotFoundException $e) {
                    continue;
                }
            }
            if (empty($uris)) {
                continue;
            }

            $source = $this->getImageVariationSource(
                $uris,
                $sourceReqs['media'],
                $baseVariation,
                $sourceVariationName
            );
            $sources[$sourceVariationName] = $source;
        }
        return $sources;
    }

    private function getImageVariationSource(
        array      $uris,
        $media,
        ?Variation $baseVariation,
        string     $sourceVariationName
    ): ImageSource {
        $source = new ImageSource(
            implode(', ', $uris),
            $media,
            $baseVariation instanceof ImageVariation ? $baseVariation->width : null,
            $baseVariation instanceof ImageVariation ? $baseVariation->height : null,
            $baseVariation instanceof ImageVariation ? $baseVariation->fileSize : null,
            $baseVariation instanceof FocusedVariation ? new ImageFocusPoint(
                $baseVariation->focusPoint->getPosX(),
                $baseVariation->focusPoint->getPosY()
            ) : null,
            $baseVariation ? $baseVariation->mimeType : null,
            $sourceVariationName
        );
        return $source;
    }
}
