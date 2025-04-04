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

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use ErdnaxelaWeb\StaticFakeDesign\Value\File;
use Ibexa\Bundle\Core\EventListener\ContentDownloadRouteReferenceListener;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\MVC\Symfony\Routing\Generator\RouteReferenceGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class FileFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected RouterInterface $router,
        protected RouteReferenceGenerator $routeReferenceGenerator,
    ) {
    }

    public function transformFieldValue(
        AbstractContent $content,
        string $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): ?File {
        /** @var \Ibexa\Core\FieldType\BinaryFile\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        if (isset($fieldValue->fileName)) {
            $routeReference = $this->routeReferenceGenerator->generate(
                ContentDownloadRouteReferenceListener::ROUTE_NAME,
                [
                    ContentDownloadRouteReferenceListener::OPT_CONTENT => $content->innerContent,
                    ContentDownloadRouteReferenceListener::OPT_VERSION => $content->getVersionInfo()->versionNo,
                    ContentDownloadRouteReferenceListener::OPT_FIELD_IDENTIFIER => $fieldIdentifier,
                ]
            );
            $downloadUri = $this->router->generate(
                $routeReference->getRoute(),
                $routeReference->getParams(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return new File($fieldValue->fileName, $fieldValue->fileSize, $fieldValue->mimeType, $downloadUri);
        }

        return null;
    }
}
