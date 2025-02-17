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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Templating\Twig;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaContent;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Extension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getContentViewControllerParameters', [$this, 'getContentViewControllerParameters']),
        ];
    }

    public function getContentViewControllerParameters($content, array $parameters): array
    {
        if ($content instanceof Content) {
            if (in_array('id', $content->getLazyObjectState()->skippedProperties)) {
                $parameters['contentId'] = $content->id;
            }
            if (in_array('locationId', $content->getLazyObjectState()->skippedProperties)) {
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
