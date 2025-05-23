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

    /**
     * @param Content|IbexaContent|array<string, mixed>      $content
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    public function getContentViewControllerParameters(mixed $content, array $parameters): array
    {
        if ($content instanceof Content) {
            if (array_key_exists('id', $content->getLazyObjectState()->skippedProperties)) {
                $parameters['contentId'] = $content->id;
            }
            if (array_key_exists('locationId', $content->getLazyObjectState()->skippedProperties)) {
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
