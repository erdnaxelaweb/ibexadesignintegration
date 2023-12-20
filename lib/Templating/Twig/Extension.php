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
        if ($content instanceof IbexaContent) {
            $parameters['content'] = $content;
        } else {
            $parameters = array_merge($content, $parameters);
        }

        return $parameters;
    }
}
