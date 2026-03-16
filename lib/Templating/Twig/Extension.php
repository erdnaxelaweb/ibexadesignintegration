<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Templating\Twig;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaContent;

class Extension
{
    /**
     * @param Content|IbexaContent|array<string, mixed>      $content
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    #[\Twig\Attribute\AsTwigFunction(name: 'getContentViewControllerParameters')]
    public function getContentViewControllerParameters(mixed $content, array $parameters): array
    {
        if ($content instanceof Content) {
            if (in_array('id', $content->nonLazyProperties, true)) {
                $parameters['contentId'] = $content->id;
            }
            if (in_array('locationId', $content->nonLazyProperties, true)) {
                $parameters['locationId'] = $content->locationId;
            }
        } elseif ($content instanceof IbexaContent) {
            $parameters['contentId'] = $content->id;
        } else {
            $parameters = array_merge($content, $parameters);
        }

        return $parameters;
    }
}
