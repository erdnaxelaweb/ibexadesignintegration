<?php
declare( strict_types=1 );

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\Controller\Api;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerBuilder;
use ErdnaxelaWeb\StaticFakeDesign\Fake\Generator\PagerGenerator;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class PagerController extends AbstractController
{
    public function __construct(
        protected PagerBuilder $pagerBuilder,
        protected PagerGenerator $pagerGenerator,
        protected ConfigResolverInterface $configResolver,
        protected SerializerInterface $serializer
    )
    {
    }

    public function getPager( string $type, Request $request  ): JsonResponse
    {
        if($this->configResolver->getParameter('enable_fake_generation', 'ibexa_design_integration') === true) {
            $pager = ($this->pagerGenerator)($type);
        }else{
            $context = $request->get('context', []);
            $pager = $this->pagerBuilder->build( $type, $context );
        }

        return new JsonResponse($this->serializer->serialize([
            'currentPage' => $pager->getCurrentPage(),
            'itemsPerPage' => $pager->getMaxPerPage(),
            'totalItems' => $pager->getNbResults(),
            'items' => $pager->getCurrentPageResults()
        ], 'json'), json: true);
    }
}
