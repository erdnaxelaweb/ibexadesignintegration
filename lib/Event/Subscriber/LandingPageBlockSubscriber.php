<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Event\Subscriber;

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockTransformer;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Definition\BlockDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Exception\DefinitionNotFoundException;
use Ibexa\FieldTypePage\FieldType\Page\Block\Renderer\BlockRenderEvents;
use Ibexa\FieldTypePage\FieldType\Page\Block\Renderer\Event\PreRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LandingPageBlockSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected BlockTransformer $blockTransformer,
        protected DefinitionManager $definitionManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BlockRenderEvents::GLOBAL_BLOCK_RENDER_PRE => 'onBlockPreRender',
        ];
    }

    public function onBlockPreRender(PreRenderEvent $event)
    {
        $blockValue = $event->getBlockValue();
        try {
            $this->definitionManager->getDefinition(BlockDefinition::class, $blockValue->getType());
            /** @var \Ibexa\FieldTypePage\FieldType\Page\Block\Renderer\Twig\TwigRenderRequest $renderRequest */
            $renderRequest = $event->getRenderRequest();
            $parameters = $renderRequest->getParameters();
            $parameters['block'] = ($this->blockTransformer)($blockValue);
            $renderRequest->setParameters($parameters);
        } catch (DefinitionNotFoundException $e) {
            return;
        }
    }
}
