<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CatalogSearchRestApi\Api\Storefront\Mapper;

use ArrayObject;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\FacetConfigTransfer;
use Generated\Shared\Transfer\FacetSearchResultTransfer;
use Generated\Shared\Transfer\PriceModeConfigurationTransfer;
use Generated\Shared\Transfer\RangeSearchResultTransfer;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class CatalogSearchResourceMapper extends AbstractCatalogSearchResourceMapper implements CatalogSearchResourceMapperInterface
{
    protected const string SEARCH_KEY_PRODUCTS = 'products';

    protected const string SEARCH_KEY_FACETS = 'facets';

    protected const string PRICE_AMOUNT_KEY_GROSS = 'grossAmount';

    protected const string PRICE_AMOUNT_KEY_NET = 'netAmount';

    /**
     * {@inheritDoc}
     *
     * @param array<string, mixed> $searchResult
     *
     * @return array<string, mixed>
     */
    public function mapSearchResultToResourceData(
        array $searchResult,
        CurrencyTransfer $currencyTransfer,
        PriceModeConfigurationTransfer $priceModeConfigurationTransfer,
    ): array {
        $convertedSearchResult = $this->convertSearchResultKeysToArray($searchResult);
        $products = $this->mapProducts($searchResult, $currencyTransfer, $priceModeConfigurationTransfer);
        $facets = $this->mapFacets($searchResult);

        return [
            'catalogSearchId' => 'catalog-search',
            'sort' => $convertedSearchResult['sort'] ?? [],
            'pagination' => $convertedSearchResult['pagination'] ?? [],
            'abstractProducts' => $products,
            'valueFacets' => $facets['valueFacets'],
            'rangeFacets' => $facets['rangeFacets'],
            'spellingSuggestion' => $convertedSearchResult['spellingSuggestion'] ?? null,
            'categoryTreeFilter' => $convertedSearchResult['categoryTreeFilter'] ?? [],
        ];
    }

    /**
     * @param array<string, mixed> $searchResult
     *
     * @return array<string, mixed>
     */
    protected function convertSearchResultKeysToArray(array $searchResult): array
    {
        $convertedSearchResult = [];

        foreach ($searchResult as $key => $item) {
            $convertedSearchResult[$key] = $this->convertSearchResultItem($item);
        }

        return $convertedSearchResult;
    }

    protected function convertSearchResultItem(mixed $item): mixed
    {
        if ($item instanceof ArrayObject) {
            return $this->convertTransferCollectionToArray($item);
        }

        if ($item instanceof TransferInterface) {
            return $this->convertKeysToCamelCase($item->toArray());
        }

        return $item;
    }

    /**
     * @param \ArrayObject<int|string, mixed> $transferCollection
     *
     * @return array<int, mixed>
     */
    protected function convertTransferCollectionToArray(ArrayObject $transferCollection): array
    {
        $converted = [];

        foreach ($transferCollection as $item) {
            $converted[] = $item instanceof TransferInterface
                ? $this->convertKeysToCamelCase($item->toArray())
                : $item;
        }

        return $converted;
    }

    /**
     * @param array<string, mixed> $searchResult
     *
     * @return array<int, array<string, mixed>>
     */
    protected function mapProducts(
        array $searchResult,
        CurrencyTransfer $currencyTransfer,
        PriceModeConfigurationTransfer $priceModeConfigurationTransfer,
    ): array {
        if (!isset($searchResult[static::SEARCH_KEY_PRODUCTS]) || !is_array($searchResult[static::SEARCH_KEY_PRODUCTS])) {
            return [];
        }

        $products = [];

        foreach ($searchResult[static::SEARCH_KEY_PRODUCTS] as $product) {
            $originalPrices = $product['prices'] ?? [];
            $product = $this->convertKeysToCamelCase($product);
            $product['prices'] = $this->mapProductPrices(
                $originalPrices,
                $currencyTransfer,
                $priceModeConfigurationTransfer,
            );
            $products[] = $product;
        }

        return $products;
    }

    /**
     * @param array<string, int> $prices
     *
     * @return array<int, array<string, mixed>>
     */
    protected function mapProductPrices(
        array $prices,
        CurrencyTransfer $currencyTransfer,
        PriceModeConfigurationTransfer $priceModeConfigurationTransfer,
    ): array {
        $mappedPrices = [];
        $currencyData = $currencyTransfer->toArray(true, true);
        $amountKey = $this->resolvePriceAmountKey($priceModeConfigurationTransfer);

        foreach ($prices as $priceType => $price) {
            $priceEntry = [
                'priceTypeName' => $priceType,
                'currency' => $currencyData,
                $priceType => $price,
            ];

            if ($amountKey !== null) {
                $priceEntry[$amountKey] = $price;
            }

            $mappedPrices[] = $priceEntry;
        }

        return $mappedPrices;
    }

    protected function resolvePriceAmountKey(PriceModeConfigurationTransfer $priceModeConfigurationTransfer): ?string
    {
        return match ($priceModeConfigurationTransfer->getCurrentPriceMode()) {
            $priceModeConfigurationTransfer->getGrossModeIdentifier() => static::PRICE_AMOUNT_KEY_GROSS,
            $priceModeConfigurationTransfer->getNetModeIdentifier() => static::PRICE_AMOUNT_KEY_NET,
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $searchResult
     *
     * @return array{valueFacets: array<int, array<string, mixed>>, rangeFacets: array<int, array<string, mixed>>}
     */
    protected function mapFacets(array $searchResult): array
    {
        $valueFacets = [];
        $rangeFacets = [];

        if (!isset($searchResult[static::SEARCH_KEY_FACETS])) {
            return ['valueFacets' => $valueFacets, 'rangeFacets' => $rangeFacets];
        }

        foreach ($searchResult[static::SEARCH_KEY_FACETS] as $facet) {
            if ($facet instanceof FacetSearchResultTransfer) {
                $facetData = $facet->toArray(true, true);
                $facetData['config'] = $this->mapFacetConfig($facet->getConfigOrFail());
                $valueFacets[] = $facetData;

                continue;
            }

            if ($facet instanceof RangeSearchResultTransfer) {
                $facetData = $facet->toArray(true, true);
                $facetData['config'] = $this->mapFacetConfig($facet->getConfigOrFail());
                $rangeFacets[] = $facetData;
            }
        }

        return ['valueFacets' => $valueFacets, 'rangeFacets' => $rangeFacets];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapFacetConfig(FacetConfigTransfer $facetConfigTransfer): array
    {
        $config = $facetConfigTransfer->toArray(true, true);
        $config['isMultiValued'] = (bool)($config['isMultiValued'] ?? false);

        return $config;
    }
}
