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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

use DateTime;
use ErdnaxelaWeb\StaticFakeDesign\Value\Breadcrumb;
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentFieldsCollection;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaApiContent;
use Ibexa\Core\Repository\Values\Content\Content as IbexaContent;

class Content extends IbexaContent
{
    public function __construct(
        protected IbexaApiContent $innerContent,
        public readonly string $name,
        public readonly string $type,
        public readonly DateTime $creationDate,
        public readonly DateTime $modificationDate,
        ContentFieldsCollection  $fields,
        public readonly string $url,
        public readonly Breadcrumb  $breadcrumb
    ) {
        parent::__construct(
            [
                'thumbnail' => $innerContent->thumbnail,
                'versionInfo' => $innerContent->versionInfo,
                'contentType' => $innerContent->contentType,
                'internalFields' => [],
                'prioritizedFieldLanguageCode' => $innerContent->prioritizedFieldLanguageCode,
            ]
        );
        $this->fields = $fields;
    }

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->innerContent, $name], $arguments);
    }
}
