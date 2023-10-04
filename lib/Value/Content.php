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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

use DateTime;
use ErdnaxelaWeb\StaticFakeDesign\Value\Breadcrumb;
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentFieldsCollection;
use Ibexa\Core\Repository\Values\Content\Content as IbexaContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaApiContent;

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
    )
    {
        parent::__construct(
            [
                'thumbnail' => $innerContent->thumbnail,
                'versionInfo' => $innerContent->versionInfo,
                'contentType' => $innerContent->contentType,
                'internalFields' => [],
                'prioritizedFieldLanguageCode' => $innerContent->prioritizedFieldLanguageCode
            ]
        );
        $this->fields = $fields;
    }

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->innerContent, $name], $arguments);
    }

}
