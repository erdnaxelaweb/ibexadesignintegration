<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
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
        protected ChainFilterHandler            $filterHandler,
        protected RequestStack              $requestStack,
        protected LinkGenerator $linkGenerator,
    ) {
    }

    public function buildList(
        string $searchFormName,
        PagerDefinition $pagerDefinition,
        FormInterface $filtersFormBuilder,
        SearchData $searchData
    ): array {
        $links = [];
        foreach ($searchData->filters as $filter => $filterValue) {
            if (empty($filterValue)) {
                continue;
            }
            $pagerFilterDefinition = $pagerDefinition->getFilter($filter);

            $labels = $this->filterHandler->getValuesLabels(
                $pagerFilterDefinition->getType(),
                is_array($filterValue) ? $filterValue : [$filterValue],
                $filtersFormBuilder->get('filters')
                    ->get($filter)
            );

            $query = $this->getRequest()
                ->query->all();

            $query[$searchFormName]['search'] = '';

            if (is_array($filterValue)) {
                foreach ($filterValue as $value) {
                    $valueKey = array_search($value, $query[$searchFormName]['filters'][$filter] ?? []);
                    unset($query[$searchFormName]['filters'][$filter][$valueKey]);
                    $links[] = $this->generateLink($labels[$value] ?? $value, $query, [
                        'extras' => [
                            'filter' => $filter,
                            'value' => $value,
                        ],
                    ]);
                }
            } else {
                unset($query[$searchFormName]['filters'][$filter]);

                $links[] = $this->generateLink($labels[$filterValue] ?? $filterValue, $query, [
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

    protected function generateLink(string $label, array $query, array $options): ItemInterface
    {
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
