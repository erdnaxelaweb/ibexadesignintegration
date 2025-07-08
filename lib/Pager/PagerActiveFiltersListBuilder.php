<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Helper\LinkGenerator;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\ChainFilterHandler;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use Knp\Menu\ItemInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PagerActiveFiltersListBuilder
{
    public function __construct(
        protected ChainFilterHandler $filterHandler,
        protected RequestStack $requestStack,
        protected LinkGenerator $linkGenerator,
    ) {
    }

    /**
     * @return ItemInterface[]
     */
    public function buildList(
        string $searchFormName,
        PagerDefinition $pagerDefinition,
        FormInterface $filtersFormBuilder,
        SearchData $searchData
    ): array {
        $links = [];
        $flattenedFiltersList = $this->filterHandler->getFlattenedFiltersList($pagerDefinition);
        foreach ($searchData->filters as $filter => $filterValue) {
            if (empty($filterValue)) {
                continue;
            }
            $pagerFilterDefinition = $flattenedFiltersList[$filter] ?? null;
            if (!$pagerFilterDefinition) {
                continue;
            }

            if (!$filtersFormBuilder->get('filters')->has($filter)) {
                continue;
            }

            $labels = $this->filterHandler->getValuesLabels(
                $pagerFilterDefinition->getType(),
                $filterValue,
                $filtersFormBuilder->get('filters')
                    ->get($filter)
            );

            $query = $this->getRequest()
                ->query->all();

            if (is_array($labels)) {
                foreach ($filterValue as $value) {
                    $iterationQuery = $query;
                    $valueKey = array_search($value, $iterationQuery[$searchFormName]['filters'][$filter] ?? [], true);
                    unset($iterationQuery[$searchFormName]['filters'][$filter][$valueKey]);
                    $links[] = $this->generateLink($labels[$value] ?? $value, $iterationQuery, [
                        'extras' => [
                            'filter' => $filter,
                            'value' => $value,
                        ],
                    ]);
                }
            } else {
                unset($query[$searchFormName]['filters'][$filter]);

                $links[] = $this->generateLink($labels ?? $filterValue, $query, [
                    'extras' => [
                        'filter' => $filter,
                        'value' => $filterValue,
                    ],
                ]);
            }
        }
        return $links;
    }

    protected function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @param array<string, mixed>  $query
     * @param array<string, mixed>  $options
     */
    protected function generateLink(string $label, array $query, array $options): ItemInterface
    {
        $options['extras']['query'] = urldecode(http_build_query($query));
        return $this->linkGenerator->generateLink(
            $this->linkGenerator->generateUrl(
                $this->getRequest()
                    ->attributes->get('_route'),
                array_merge($query, $this->getRequest()->attributes->get('_route_params', []))
            ),
            $label,
            $options
        );
    }
}
