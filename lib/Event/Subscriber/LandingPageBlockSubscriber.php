<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Event\Subscriber;

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockTransformer;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\BlockConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Exception\ConfigurationNotFoundException;
use Ibexa\FieldTypePage\FieldType\Page\Block\Renderer\BlockRenderEvents;
use Ibexa\FieldTypePage\FieldType\Page\Block\Renderer\Event\PreRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LandingPageBlockSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected BlockTransformer $blockTransformer,
        protected BlockConfigurationManager $blockConfigurationManager
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
            $this->blockConfigurationManager->getConfiguration($blockValue->getType());
            $renderRequest = $event->getRenderRequest();
            $parameters = $renderRequest->getParameters();
            $parameters['block'] = ($this->blockTransformer)($blockValue);
            $renderRequest->setParameters($parameters);
        } catch (ConfigurationNotFoundException $e) {
            return;
        }
    }
}
