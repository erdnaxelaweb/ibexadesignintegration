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
use ErdnaxelaWeb\IbexaDesignIntegration\Value\Content;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\ContentConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentFieldsCollection;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as IbexaLocation;
use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Symfony\Component\Routing\RouterInterface;

class ContentTransformer
{
    /**
     * @var \ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue\FieldValueTransformerInterface[]
     */
    protected array $fieldValueTransformers = [];

    public function __construct(
        protected ContentConfigurationManager $contentConfigurationManager,
        protected RouterInterface $router,
        protected BreadcrumbGenerator $breadcrumbGenerator,
        iterable $fieldValueTransformers
    ) {
        foreach ($fieldValueTransformers as $type => $fieldValueTransformer) {
            $this->fieldValueTransformers[$type] = $fieldValueTransformer;
        }
    }

    public function __invoke(IbexaContent $ibexaContent, ?IbexaLocation $ibexaLocation = null): Content
    {
        $ibexaLocation = $ibexaLocation ?? $ibexaContent->contentInfo->getMainLocation();
        $contentType = $ibexaContent->getContentType();
        $contentTypeIdentifier = $contentType->identifier;
        $contentConfiguration = $this->contentConfigurationManager->getConfiguration($contentTypeIdentifier);

        $contentFields = new ContentFieldsCollection();
        foreach ($contentConfiguration['fields'] as $fieldIdentifier => $fieldConfiguration) {
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
            $contentFields->set($fieldIdentifier, $fieldValue);
        }

        return new Content(
            $ibexaContent,
            $ibexaContent->getName(),
            $contentTypeIdentifier,
            $ibexaContent->contentInfo->publishedDate,
            $ibexaContent->contentInfo->modificationDate,
            $contentFields,
            $this->router->generate(UrlAliasRouter::URL_ALIAS_ROUTE_NAME, [
                'locationId' => $ibexaLocation->id,
            ]),
            $this->breadcrumbGenerator->generateLocationBreadcrumb($ibexaLocation)
        );
    }
}
