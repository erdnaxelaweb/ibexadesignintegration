<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\Controller;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PagerRenderController
{
    public function __construct(
        protected ConfigResolverInterface $configResolver,
        protected UrlAliasGenerator $urlAliasGenerator,
        protected HttpClientInterface $httpClient,
        protected RequestStack    $requestStack,
        protected RouterInterface $router,
        protected string $searchAppUrl,
        protected string $searchAppDevUrl,
    ) {
    }

    public function __invoke(string $id, string $pagerType, array $parameters = []): Response
    {
        $request = $this->requestStack->getMainRequest();

        $qs = http_build_query(
            [
                'appId' => $id,
                'apiUrl' => $this->router->generate(
                    'ibexa_design_integration.api.pager',
                    [
                        'type' => $pagerType,
                    ] + $parameters,
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'pathPrefix' => $this->getRootPathPrefix(),
            ]
        );
        if (null !== $requestQs = $request->getQueryString()) {
            $qs .= '&' . $requestQs;
        }

        $uri = $this->searchAppUrl . $request->getPathInfo() . '?' . $qs;

        $response = $this->httpClient->request(
            'GET',
            $uri,
            [
                'headers' => [
                    'cookie' => $request->headers->get('cookie'),
                ],
            ]
        );

        try {
            $content = $response->getContent();
            if (strpos($content, '/@vite/client')) {
                $content = str_replace(
                    [
                        'src="/',
                        'from "/',
                    ],
                    [
                        sprintf('src="%s/', $this->searchAppDevUrl),
                        sprintf('from "%s/', $this->searchAppDevUrl),
                    ],
                    $content
                );
            }

            $content = str_replace(
                '<script',
                '<script async=""',
                $content
            );
        } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            $content = $e->getMessage();
        }
        return new Response($content);
    }

    public function getRootPathPrefix(): string
    {
        $rootLocationId = $this->configResolver->getParameter('content.tree_root.location_id');
        return $this->urlAliasGenerator->getPathPrefixByRootLocationId(
            $rootLocationId
        );
    }
}
