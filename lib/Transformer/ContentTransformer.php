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

declare(strict_types=1);

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer;

use ErdnaxelaWeb\IbexaDesignIntegration\Helper\BreadcrumbGenerator;
use ErdnaxelaWeb\IbexaDesignIntegration\Helper\LinkGenerator;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\Content;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\ContentFieldsCollection;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\ContentConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Value\Breadcrumb;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as IbexaLocation;
use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter;

class ContentTransformer
{
    public function __construct(
        protected ContentConfigurationManager $contentConfigurationManager,
        protected LinkGenerator $linkGenerator,
        protected BreadcrumbGenerator $breadcrumbGenerator,
        protected FieldValueTransformer $fieldValueTransformers
    ) {
    }

    public function __invoke(IbexaContent $ibexaContent, ?IbexaLocation $ibexaLocation = null): Content
    {
        if ($ibexaContent instanceof Content) {
            return $ibexaContent;
        }

        $ibexaLocation = $ibexaLocation ?? $ibexaContent->contentInfo->getMainLocation();
        $contentType = $ibexaContent->getContentType();
        $contentTypeIdentifier = $contentType->identifier;
        $contentConfiguration = $this->contentConfigurationManager->getConfiguration($contentTypeIdentifier);

        $contentFields = new ContentFieldsCollection(
            $ibexaContent,
            $contentType,
            $contentConfiguration['fields'],
            $this->fieldValueTransformers
        );
        return new Content(
            $ibexaContent,
            $ibexaContent->id,
            $ibexaContent->getName(),
            $contentTypeIdentifier,
            $ibexaContent->contentInfo->publishedDate ?? $ibexaContent->versionInfo->creationDate,
            $ibexaContent->contentInfo->modificationDate ?? $ibexaContent->versionInfo->modificationDate,
            $contentFields,
            $ibexaLocation && $ibexaLocation->id ? $this->linkGenerator->generateUrl(
                UrlAliasRouter::URL_ALIAS_ROUTE_NAME, [
                'locationId' => $ibexaLocation->id,
            ]) : '',
            $ibexaLocation ? $this->breadcrumbGenerator->generateLocationBreadcrumb($ibexaLocation) : new Breadcrumb()
        );
    }
}
