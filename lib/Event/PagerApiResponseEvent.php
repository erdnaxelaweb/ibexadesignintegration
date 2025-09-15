<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Event;

use Doctrine\Common\Collections\ArrayCollection;
use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Value\Pager;
use Symfony\Component\HttpFoundation\JsonResponse;

class PagerApiResponseEvent
{
    /**
     * @param ArrayCollection<string, mixed>                                                           $context
     * @param array<string, mixed>                                                           $responseData
     * @param array<string, mixed>                                                           $responseHeaders
     */
    public function __construct(
        public string $type,
        public PagerDefinition $definition,
        public Pager $pager,
        public ArrayCollection $context,
        public JsonResponse $response,
        public array $responseData,
        public array $responseHeaders = [],
        public array $cacheTags = []
    ) {
    }
}
