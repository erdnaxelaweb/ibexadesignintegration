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

use ErdnaxelaWeb\StaticFakeDesign\Value\File;
use Ibexa\Bundle\Core\EventListener\ContentDownloadRouteReferenceListener;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
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
        Content $content,
        string $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        array $fieldConfiguration
    ) {
        /** @var \Ibexa\Core\FieldType\BinaryFile\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);

        $routeReference = $this->routeReferenceGenerator->generate(
            ContentDownloadRouteReferenceListener::ROUTE_NAME,
            [
                ContentDownloadRouteReferenceListener::OPT_CONTENT => $content,
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
}
