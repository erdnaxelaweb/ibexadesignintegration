<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaHtmlIntegrationBundle/blob/master/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Helper;

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\Content;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\ImageConfiguration;
use ErdnaxelaWeb\StaticFakeDesign\Value\Image;
use ErdnaxelaWeb\StaticFakeDesign\Value\ImageFocusPoint;
use ErdnaxelaWeb\StaticFakeDesign\Value\ImageSource;
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
        protected VariationHandler $imageVariationService,
        protected ImageConfiguration $imageConfiguration,
        protected ContentService $contentService,
        protected LoggerInterface $imageVariationLogger,
        protected ContentTransformer $contentTransformer
    )
    {
    }

    /**
     * @param Field $field
     * @param VersionInfo $versionInfo
     * @param $variationName
     *
     * @return Variation|null
     */
    protected function getImageVariationIfExist( Field $field, VersionInfo $versionInfo, $variationName ): ?Variation
    {
        try
        {
            return $this->imageVariationService->getVariation(
                $field,
                $versionInfo,
                $variationName
            );
        }
        catch ( SourceImageNotFoundException $e )
        {
            if ( isset( $this->imageVariationLogger ) )
            {
                $this->imageVariationLogger->error(
                    "Couldn't create variation '{$variationName}' for image with id {$field->value->id} 
                        because source image can't be found"
                );
            }
        }
        catch ( InvalidArgumentException|InvalidVariationException|ReflectionException $e )
        {
            if ( isset( $this->imageVariationLogger ) )
            {
                $this->imageVariationLogger->error(
                    "Couldn't create variation '{$variationName}' for image with id {$field->value->id} 
                        because an image could not be created from the given input"
                );
            }
        }

        return null;
    }

    public function generateImage(
        IbexaContent $content,
        string       $fieldIdentifier,
        string       $variationName
    )
    {
        $fieldValue = $content->getFieldValue( $fieldIdentifier );
        if ( $fieldValue instanceof ImageValue )
        {
            return $this->getImage(
                ($this->contentTransformer)($content),
                $content->getField( $fieldIdentifier ),
                $variationName
            );
        }
        if ( $fieldValue instanceof ImageAssetValue && $fieldValue->destinationContentId )
        {
            $relatedContent = $this->contentService->loadContent( $fieldValue->destinationContentId );
            return $this->generateImage(
                $relatedContent,
                'image',
                $variationName
            );
        }
    }

    protected function getImage(
        Content $content,
        Field        $field,
        string       $variationName = 'original'
    ): ?Image
    {
        /** @var ImageValue $imageFieldValue */
        $imageFieldValue = $field->value;
        $sources = $this->getImageSources( $field, $content->getVersionInfo(), $variationName );

        return new Image(
            $imageFieldValue->alternativeText,
            $content->fields['caption'],
            $content->fields['credits'],
            $sources,
        );
    }

    protected function getImageSources( Field $field, VersionInfo $versionInfo, string $variationName ): array
    {
        $variationConfig = $this->imageConfiguration->getVariationConfig( $variationName );

        $sources = [];
        foreach ( $variationConfig as $sourceReqs )
        {
            $sourceVariationName = "{$variationName}_{$sourceReqs['suffix']}";
            $typeVariationNames = [
                '' => $sourceVariationName,
                ' 2x' => $sourceVariationName . '_retina'
            ];
            $uris = [];
            $baseVariation = null;

            foreach ( $typeVariationNames as $variationType => $typeVariationName )
            {
                try
                {
                    $variation = $this->getImageVariationIfExist(
                        $field,
                        $versionInfo,
                        $typeVariationName
                    );
                    $uris[] = $variation->uri . $variationType;
                    if ( !$baseVariation )
                    {
                        $baseVariation = $variation;
                    }
                }
                catch ( NonExistingFilterException $e )
                {
                    continue;
                }
            }
            if ( empty( $uris ) )
            {
                continue;
            }

            $source = new ImageSource(
                implode( ', ', $uris ),
                $sourceReqs['media'],
                $baseVariation instanceof ImageVariation ? $baseVariation->width : null,
                $baseVariation instanceof ImageVariation ? $baseVariation->height : null,
                $baseVariation instanceof FocusedVariation ? new ImageFocusPoint(
                    $baseVariation->focusPoint->getPosX(),
                    $baseVariation->focusPoint->getPosY()
                ) : null,
                $baseVariation ? $baseVariation->mimeType : null,
                $sourceVariationName
            );
            $sources[$sourceVariationName] = $source;
        }
        return $sources;
    }
}
