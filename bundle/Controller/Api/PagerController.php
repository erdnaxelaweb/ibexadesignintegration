<?php
declare( strict_types=1 );

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\Controller\Api;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerBuilder;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Fake\Generator\PagerGenerator;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Limenius\Liform\Liform;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class PagerController extends AbstractController
{
    public function __construct(
        protected PagerBuilder $pagerBuilder,
        protected PagerGenerator $pagerGenerator,
        protected DefinitionManager $definitionManager,
        protected ConfigResolverInterface $configResolver,
        protected SerializerInterface $serializer,
        protected Liform $liform
    )
    {
    }

    public function getPager( string $type, Request $request  ): JsonResponse
    {
        $pagerDefinition = $this->definitionManager->getDefinition( PagerDefinition::class, $type );
        if($pagerDefinition->getSearchType() !== 'record') {
            throw new \InvalidArgumentException('For performance reason, the pager search type must be "record"');
        }

        if($this->configResolver->getParameter('enable_fake_generation', 'ibexa_design_integration') === true) {
            $pager = ($this->pagerGenerator)($type);
        }else{
            $context = $request->get('context', []);
            $pager = $this->pagerBuilder->build( $type, $context );
        }

        $form = $this->liform->transform($pager->getFiltersForm());

        $response = new JsonResponse($this->serializer->serialize([
            'currentPage' => $pager->getCurrentPage(),
            'itemsPerPage' => $pager->getMaxPerPage(),
            'totalItems' => $pager->getNbResults(),
            'items' => $pager->getCurrentPageResults(),
            'filters' => $form
        ], 'json'),
            json: true
        );

        return $response;
    }
}
