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

use Doctrine\Common\Collections\ArrayCollection;
use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Event\PagerApiResponseEvent;
use ErdnaxelaWeb\IbexaDesignIntegration\Normalizer\FormViewNormalizer;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerBuilder;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Fake\Generator\PagerGenerator;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Knp\Menu\ItemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PagerController extends AbstractController
{
    public function __construct(
        protected PagerBuilder $pagerBuilder,
        protected PagerGenerator $pagerGenerator,
        protected DefinitionManager $definitionManager,
        protected ConfigResolverInterface $configResolver,
        protected SerializerInterface $serializer,
        protected FormViewNormalizer $formViewNormalizer,
        protected EventDispatcherInterface $eventDispatcher,
        protected ContentTagInterface $responseTagger
    ) {
    }

    public function getPager(string $type, Request $request): JsonResponse
    {
        $pagerDefinition = $this->definitionManager->getDefinition(PagerDefinition::class, $type);
        if ($pagerDefinition->getSearchType() !== 'document') {
            throw new \InvalidArgumentException('For performance reason, the pager search type must be "document"');
        }

        $pagerParameters = $request->get($type, null);
        $pagerContext = new ArrayCollection($pagerParameters['ctx'] ?? []);

        if ($this->configResolver->getParameter('enable_fake_generation', 'ibexa_design_integration') === true) {
            $pager = ($this->pagerGenerator)($type);
        } else {
            $pager = $this->pagerBuilder->build($type, $pagerContext);
        }

        $cacheTags = [];

        /** @var \ErdnaxelaWeb\StaticFakeDesign\Value\Document[] $currentPageResults */
        $currentPageResults = $pager->getCurrentPageResults();
        foreach ($currentPageResults as $currentPageResult) {
            $cacheTags[] = sprintf('d-%s', $currentPageResult->getShortType());
            $cacheTags[] = $currentPageResult->cacheTag();
        }

        $form = ($this->formViewNormalizer)($pager->getFiltersForm());
        $activeFilters = array_map(function (ItemInterface $item) {
            return [
                'name' => $item->getName(),
                'uri' => $item->getUri(),
                'extras' => $item->getExtras(),
            ];
        }, $pager->getActiveFilters());

        $responseData = [
            'activeFilters' => $activeFilters,
            'searchForm' => $form,
            'currentPage' => $pager->getCurrentPage(),
            'itemsPerPage' => $pager->getMaxPerPage(),
            'totalPages' => $pager->getNbPages(),
            'totalItems' => $pager->getNbResults(),
            'items' => $currentPageResults,
            'context' => $pagerContext,
        ];

        $responseEvent = new PagerApiResponseEvent(
            $type,
            $pagerDefinition,
            $pager,
            $pagerContext,
            $responseData,
            [],
            $cacheTags
        );

        $this->eventDispatcher->dispatch($responseEvent);

        $response = new JsonResponse(
            $this->serializer->serialize($responseEvent->responseData, 'json'),
            json: true
        );

        $this->setResponseCacheHeaders($response, $responseEvent->cacheTags);

        if ($request->headers->has('Origin')) {
            $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin'), true);
            $response->headers->set('Access-Control-Allow-Credentials', 'true', true);
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization', true);
            $response->headers->set('Access-Control-Allow-Methods', 'OPTIONS, GET, POST', true);
        }

        foreach ($responseEvent->responseHeaders as $header => $value) {
            $response->headers->set($header, $value);
        }

        return $response;
    }

    protected function setResponseCacheHeaders(
        JsonResponse $response,
        array $cacheTags = []
    ): void {
        if (!$this->configResolver->getParameter('content.view_cache')) {
            return;
        }
        $response->setPublic();

        if ($this->configResolver->getParameter('content.ttl_cache')) {
            $response->setSharedMaxAge((int) $this->configResolver->getParameter('content.default_ttl'));
        }
        $response->headers->set('original-cache-control', $response->headers->get('cache-control'));
        if (!empty($cacheTags)) {
            $this->responseTagger->addTags($cacheTags);
        }
    }
}
