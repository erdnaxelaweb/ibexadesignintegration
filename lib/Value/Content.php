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

use Ibexa\Core\Repository\Values\Content\Content as IbexaContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaApiContent;

class Content extends IbexaContent
{
    public function __construct(
        protected IbexaApiContent $innerContent,
        public readonly string    $name,
        protected                 $fields,
        public readonly string    $url,
        public readonly array     $breadcrumb
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
    }

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->innerContent, $name], $arguments);
    }

}
