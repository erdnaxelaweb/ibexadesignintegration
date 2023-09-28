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

declare(strict_types=1);

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\Content;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\ContentConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentFieldsCollection;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaContent;
use Ibexa\Core\FieldType\RelationList\Value;

class ContentTransformer
{
    public function __construct(

        protected ContentConfigurationManager $contentConfigurationManager
    )
    {
    }

    public function __invoke(IbexaContent $ibexaContent): Content
    {
        $contentTypeIdentifier = $ibexaContent->getContentType()->identifier;
        $contentConfiguration = $this->contentConfigurationManager->getConfiguration($contentTypeIdentifier);

        $contentFields = new ContentFieldsCollection();
        foreach ($contentConfiguration['fields'] as $fieldIdentifier => $fieldConfiguration) {
            $fieldValue = $ibexaContent->getFieldValue($fieldIdentifier);
            $contentFields->set($fieldIdentifier, $this->transformFieldValue($fieldValue));
        }

        return new Content(
            $ibexaContent,
            $ibexaContent->getName(),
            $contentFields,
            "",
            []
        );
    }

    protected function transformFieldValue($ibexaFieldValue) {
        if ($ibexaFieldValue instanceof Value) {
            $fieldValue = [];
            foreach ($ibexaFieldValue->destinationContentIds as $destinationContentId) {
                $fieldValue[] = [
                    'contentId' => $destinationContentId
                ];
            };
            return $fieldValue;
        }
        return (string) $ibexaFieldValue;
    }
}
