<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use ErdnaxelaWeb\StaticFakeDesign\Expression\ExpressionResolver;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class ExpressionFieldValueTransformer extends AbstractFieldValueTransformer
{
    public function __construct(
        protected ExpressionResolver $expressionResolver,
        protected TagAwareAdapterInterface $cache
    ) {
    }

    public function support(?string $ibexaFieldTypeIdentifier): bool
    {
        return $ibexaFieldTypeIdentifier === null;
    }


    protected function transformFieldValue(
        AbstractContent        $content,
        string                 $fieldIdentifier,
        ?FieldDefinition       $ibexaFieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ) {
        $expression = $contentFieldDefinition->getOption('expression');
        $cacheTagsExpression = $contentFieldDefinition->getOption('cacheTagsExpression');

        if ($cacheTagsExpression) {
            $cacheKey = sprintf('c-%d-field-%s', $content->id, $fieldIdentifier);
            $cacheItem = $this->cache->getItem($cacheKey);
            if (!$cacheItem->isHit()) {
                $cacheTags = [];
                if (!is_array($cacheTagsExpression)) {
                    $cacheTagsExpression = [$cacheTagsExpression];
                }

                foreach ($cacheTagsExpression as $ctex) {
                    $expressionTags = ($this->expressionResolver)(
                        [
                            'content' => $content,
                        ],
                        $ctex
                    );

                    $cacheTags = array_merge(
                        $cacheTags,
                        is_array($expressionTags) ? $expressionTags : [$expressionTags]
                    );
                }



                $resolvedValue = $this->getResolvedValue($content, $expression);
                $cacheItem->set($resolvedValue);
                $cacheItem->tag(array_unique(array_filter($cacheTags)));

                $this->cache->save($cacheItem);
            }

            return $cacheItem->get();
        }

        return $this->getResolvedValue($content, $expression);
    }

    protected function getResolvedValue(AbstractContent $content, mixed $expression): mixed
    {
        return ($this->expressionResolver)(
            [
                'content' => $content,
            ],
            $expression
        );
    }
}
