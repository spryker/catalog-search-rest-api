<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CatalogSearchRestApi\Api\Storefront\Mapper;

class CatalogSearchSuggestionsResourceMapper extends AbstractCatalogSearchResourceMapper implements CatalogSearchSuggestionsResourceMapperInterface
{
    protected const string SEARCH_RESPONSE_COMPLETION_KEY = 'completion';

    protected const string SEARCH_RESPONSE_SUGGESTION_BY_TYPE_KEY = 'suggestionByType';

    protected const string SEARCH_RESPONSE_PRODUCT_ABSTRACT_KEY = 'product_abstract';

    protected const string SEARCH_RESPONSE_CATEGORY_KEY = 'category';

    protected const string SEARCH_RESPONSE_CMS_PAGE_KEY = 'cms_page';

    protected const string SEARCH_RESPONSE_NAME_KEY = 'name';

    protected const string SEARCH_RESPONSE_URL_KEY = 'url';

    protected const string SEARCH_RESPONSE_ID_CATEGORY_KEY = 'id_category';

    protected const string SEARCH_RESPONSE_PRICE_KEY = 'price';

    protected const string SEARCH_RESPONSE_ABSTRACT_NAME_KEY = 'abstract_name';

    protected const string SEARCH_RESPONSE_ABSTRACT_SKU_KEY = 'abstract_sku';

    protected const string SEARCH_RESPONSE_IMAGES_KEY = 'images';

    protected const string SEARCH_RESPONSE_IMAGE_EXTERNAL_URL_SMALL_KEY = 'external_url_small';

    protected const string SEARCH_RESPONSE_IMAGE_EXTERNAL_URL_LARGE_KEY = 'external_url_large';

    /**
     * {@inheritDoc}
     *
     * @param array<string, mixed> $suggestionsResult
     *
     * @return array<string, mixed>
     */
    public function mapSuggestionsResultToResourceData(array $suggestionsResult): array
    {
        $products = $this->mapProductSuggestions($suggestionsResult);
        $categories = $this->mapCategorySuggestions($suggestionsResult);
        $cmsPages = $this->mapCmsPageSuggestions($suggestionsResult);

        return [
            'catalogSearchSuggestionId' => 'catalog-search-suggestions',
            'completion' => $suggestionsResult[static::SEARCH_RESPONSE_COMPLETION_KEY] ?? [],
            'abstractProducts' => $products,
            'categories' => $this->mapSuggestions(
                $suggestionsResult,
                static::SEARCH_RESPONSE_CATEGORY_KEY,
                [static::SEARCH_RESPONSE_NAME_KEY, static::SEARCH_RESPONSE_URL_KEY],
            ),
            'cmsPages' => $this->mapSuggestions(
                $suggestionsResult,
                static::SEARCH_RESPONSE_CMS_PAGE_KEY,
                [static::SEARCH_RESPONSE_NAME_KEY, static::SEARCH_RESPONSE_URL_KEY],
            ),
            'categoryCollection' => $categories,
            'cmsPageCollection' => $cmsPages,
        ];
    }

    /**
     * @param array<string, mixed> $suggestionsResult
     *
     * @return array<int, array<string, mixed>>
     */
    protected function mapProductSuggestions(array $suggestionsResult): array
    {
        if (!$this->checkSuggestionByTypeValues($suggestionsResult, static::SEARCH_RESPONSE_PRODUCT_ABSTRACT_KEY)) {
            return [];
        }

        return array_map(
            fn (array $product): array => $this->convertKeysToCamelCase($this->filterProductSuggestionFields($product)),
            $suggestionsResult[static::SEARCH_RESPONSE_SUGGESTION_BY_TYPE_KEY][static::SEARCH_RESPONSE_PRODUCT_ABSTRACT_KEY],
        );
    }

    /**
     * @param array<string, mixed> $product
     *
     * @return array<string, mixed>
     */
    protected function filterProductSuggestionFields(array $product): array
    {
        $requiredKeys = [
            static::SEARCH_RESPONSE_PRICE_KEY,
            static::SEARCH_RESPONSE_ABSTRACT_NAME_KEY,
            static::SEARCH_RESPONSE_ABSTRACT_SKU_KEY,
            static::SEARCH_RESPONSE_URL_KEY,
            static::SEARCH_RESPONSE_IMAGES_KEY,
        ];

        $filtered = array_intersect_key($product, array_flip($requiredKeys));

        if (isset($filtered[static::SEARCH_RESPONSE_IMAGES_KEY]) && is_array($filtered[static::SEARCH_RESPONSE_IMAGES_KEY])) {
            $filtered[static::SEARCH_RESPONSE_IMAGES_KEY] = $this->filterImageFields($filtered[static::SEARCH_RESPONSE_IMAGES_KEY]);
        }

        return $filtered;
    }

    /**
     * @param array<int, array<string, mixed>> $images
     *
     * @return array<int, array<string, mixed>>
     */
    protected function filterImageFields(array $images): array
    {
        $imageKeys = [
            static::SEARCH_RESPONSE_IMAGE_EXTERNAL_URL_SMALL_KEY,
            static::SEARCH_RESPONSE_IMAGE_EXTERNAL_URL_LARGE_KEY,
        ];

        return array_map(
            fn (array $image): array => array_intersect_key($image, array_flip($imageKeys)),
            $images,
        );
    }

    /**
     * @param array<string, mixed> $suggestionsResult
     *
     * @return array<int, array<string, mixed>>
     */
    protected function mapCategorySuggestions(array $suggestionsResult): array
    {
        return $this->mapSuggestions(
            $suggestionsResult,
            static::SEARCH_RESPONSE_CATEGORY_KEY,
            [static::SEARCH_RESPONSE_NAME_KEY, static::SEARCH_RESPONSE_URL_KEY, static::SEARCH_RESPONSE_ID_CATEGORY_KEY],
        );
    }

    /**
     * @param array<string, mixed> $suggestionsResult
     *
     * @return array<int, array<string, mixed>>
     */
    protected function mapCmsPageSuggestions(array $suggestionsResult): array
    {
        return $this->mapSuggestions(
            $suggestionsResult,
            static::SEARCH_RESPONSE_CMS_PAGE_KEY,
            [static::SEARCH_RESPONSE_NAME_KEY, static::SEARCH_RESPONSE_URL_KEY],
        );
    }

    /**
     * @param array<string, mixed> $suggestionsResult
     * @param array<string> $keysRequired
     *
     * @return array<int, array<string, mixed>>
     */
    protected function mapSuggestions(array $suggestionsResult, string $suggestionName, array $keysRequired): array
    {
        if (!$this->checkSuggestionByTypeValues($suggestionsResult, $suggestionName)) {
            return [];
        }

        return $this->filterArrayValuesByKeys(
            $suggestionsResult[static::SEARCH_RESPONSE_SUGGESTION_BY_TYPE_KEY][$suggestionName],
            $keysRequired,
        );
    }

    protected function checkSuggestionByTypeValues(array $suggestionsResult, string $checkKey): bool
    {
        return isset($suggestionsResult[static::SEARCH_RESPONSE_SUGGESTION_BY_TYPE_KEY][$checkKey])
            && is_array($suggestionsResult[static::SEARCH_RESPONSE_SUGGESTION_BY_TYPE_KEY][$checkKey]);
    }

    /**
     * @param array<int, array<string, mixed>> $source
     * @param array<string> $keysRequired
     *
     * @return array<int, array<string, mixed>>
     */
    protected function filterArrayValuesByKeys(array $source, array $keysRequired): array
    {
        $result = [];

        foreach ($source as $data) {
            if (!array_diff_key(array_flip($keysRequired), $data)) {
                $result[] = array_intersect_key($data, array_flip($keysRequired));
            }
        }

        return $result;
    }
}
