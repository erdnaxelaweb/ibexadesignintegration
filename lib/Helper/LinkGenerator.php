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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Helper;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class LinkGenerator
{
    public function __construct(
        protected FactoryInterface $factory,
        protected RouterInterface  $router
    ) {
    }

    public function generateLocationLink(Location $location): ItemInterface
    {
        return $this->generateLink(
            $this->generateUrl(UrlAliasRouter::URL_ALIAS_ROUTE_NAME, [
                'locationId' => $location->id,
            ]),
            $location->getContent()
                ->getName()
        );
    }

    public function generateContentLink(Content $content): ItemInterface
    {
        return $this->generateLocationLink($content->contentInfo->getMainLocation());
    }

    public function generateUrl(
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        return $this->router->generate($name, $parameters, $referenceType);
    }

    public function generateLink(string $url, string $label, array $options = []): ItemInterface
    {
        $options['uri'] = $url;
        return $this->factory->createItem($label, $options);
    }
}
