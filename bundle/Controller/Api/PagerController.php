<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\Controller\Api;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Normalizer\FormViewNormalizer;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerBuilder;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Fake\Generator\PagerGenerator;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Knp\Menu\ItemInterface;
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
        protected FormViewNormalizer $formViewNormalizer,
    ) {
    }

    public function getPager(string $type, Request $request): JsonResponse
    {
        $pagerDefinition = $this->definitionManager->getDefinition(PagerDefinition::class, $type);
        if ($pagerDefinition->getSearchType() !== 'document') {
            throw new \InvalidArgumentException('For performance reason, the pager search type must be "document"');
        }

        if ($this->configResolver->getParameter('enable_fake_generation', 'ibexa_design_integration') === true) {
            $pager = ($this->pagerGenerator)($type);
        } else {
            $pagerParameters = $request->get($type, null);
            $pagerContext = $pagerParameters['ctx'] ?? [];
            $pager = $this->pagerBuilder->build($type, $pagerContext);
        }

        $currentPageResults = $pager->getCurrentPageResults();
        $form = ($this->formViewNormalizer)($pager->getFiltersForm());
        $activeFilters = array_map(function (ItemInterface $item) {
            return [
                'name' => $item->getName(),
                'uri' => $item->getUri(),
                'extras' => $item->getExtras(),
            ];
        }, $pager->getActiveFilters());

        $response = new JsonResponse(
            $this->serializer->serialize([
                'activeFilters' => $activeFilters,
                'searchForm' => $form,
                'currentPage' => $pager->getCurrentPage(),
                'itemsPerPage' => $pager->getMaxPerPage(),
                'totalPages' => $pager->getNbPages(),
                'totalItems' => $pager->getNbResults(),
                'items' => $currentPageResults,
            ], 'json'),
            json: true
        );

        if ($request->headers->has('Origin')) {
            $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin'), true);
            $response->headers->set('Access-Control-Allow-Credentials', 'true', true);
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization', true);
            $response->headers->set('Access-Control-Allow-Methods', 'OPTIONS, GET, POST', true);
        }

        return $response;
    }
}
