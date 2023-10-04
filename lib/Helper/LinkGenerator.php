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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Helper;

use eZ\Publish\API\Repository\Values\Content\Location;
use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Routing\RouterInterface;

class LinkGenerator
{
    public function __construct(protected FactoryInterface $factory,
        protected RouterInterface $router
    )
    {
    }

    public function generateLocationLink( Location $location ): ItemInterface
    {
        $name = $location->getContent()->getName();
        $options = [
            'uri' => $this->router->generate(UrlAliasRouter::URL_ALIAS_ROUTE_NAME, ['locationId' => $location->id] ),
            'linkAttributes' => [],
        ];
        return $this->factory->createItem($name, $options);
    }
}
