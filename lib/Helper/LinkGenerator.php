<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Helper;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class LinkGenerator
{
    public function __construct(
        protected FactoryInterface $factory,
        protected RouterInterface $router
    ) {
    }

    /**
     * @param array<string, mixed>                                                    $parameters
     */
    public function generateLocationLink(
        Location $location,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): ItemInterface {
        $url = '#';
        if ($location->id !== null) {
            $parameters['locationId'] = $location->id;
            $url = $this->generateUrl(UrlAliasRouter::URL_ALIAS_ROUTE_NAME, $parameters, $referenceType);
        }
        return $this->generateLink(
            $url,
            $location->getContent()
                ->getName(),
            [
                'extras' => [
                    'identifier' => $location->getContentInfo()
                        ->getContentType()
                        ->identifier,
                ],
            ]
        );
    }

    /**
     * @param array<string, mixed>                                                   $parameters
     */
    public function generateContentLink(
        Content $content,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): ItemInterface {
        return $this->generateLocationLink($content->contentInfo->getMainLocation(), $parameters, $referenceType);
    }

    /**
     * @param array<string, mixed>  $parameters
     */
    public function generateUrl(
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        return $this->router->generate($name, $parameters, $referenceType);
    }

    /**
     * @param array<string, mixed>  $options
     */
    public function generateLink(string $url, string $label, array $options = []): ItemInterface
    {
        $options['uri'] = $url;
        return $this->factory->createItem($label, $options);
    }
}
