<?php
declare( strict_types=1 );

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class PagerRenderController
{
    public function __invoke( string $uri ): Response
    {
        $content = file_get_contents($uri);
        dump($content);
        return new Response($content);
    }
}
