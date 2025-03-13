<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute;

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\Content;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;
use Ibexa\Contracts\Segmentation\SegmentationServiceInterface;

class SegmentContentMapBlockAttributeValueTransformer implements BlockAttributeValueTransformerInterface
{
    public function __construct(
        protected PermissionResolver           $permissionResolver,
        protected UserService                  $userService,
        protected SegmentationServiceInterface $segmentationService,
        protected ContentTransformer $contentTransformer,
    ) {
    }

    public function transformAttributeValue(
        BlockValue $blockValue,
        string $attributeIdentifier,
        BlockDefinition $blockDefinition,
        array $attributeConfiguration
    ) {
        return $this->getContentMapLocationId($blockValue, $attributeIdentifier);
    }
    /**
     */
    private function getContentMapLocationId(BlockValue $blockValue, string $attributeIdentifier): ?Content
    {
        try {
            $segments = $this->segmentationService->loadSegmentsAssignedToUser(
                $this->userService->loadUser(
                    $this->permissionResolver->getCurrentUserReference()->getUserId()
                )
            );
            $segmentIds = array_column($segments, 'id');
        } catch (NotFoundException $e) {
            $segmentIds = [];
        }
        if (empty($segmentIds)) {
            return null;
        }
        $contentMap = $this->getContentMap($blockValue, $attributeIdentifier);
        $locationId = null;
        foreach ($contentMap as $segmentData) {
            if (in_array($segmentData['segmentId'], $segmentIds, true)) {
                $locationId = $segmentData['locationId'];
                break;
            }
        }
        return $locationId ? $this->contentTransformer->lazyTransformContentFromLocationId((int)$locationId) : null;
    }

    private function getContentMap(BlockValue $blockValue, string $attributeIdentifier): array
    {
        $contentMapAttribute = $blockValue->getAttribute($attributeIdentifier);

        return json_decode($contentMapAttribute->getValue(), true);
    }
}
