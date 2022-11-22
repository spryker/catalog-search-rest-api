<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CatalogSearchRestApi\Processor\Mapper;

use Generated\Shared\Transfer\RestCatalogSearchSuggestionAbstractProductsTransfer;
use Generated\Shared\Transfer\RestCatalogSearchSuggestionCategoriesTransfer;
use Generated\Shared\Transfer\RestCatalogSearchSuggestionCmsPagesTransfer;
use Generated\Shared\Transfer\RestCatalogSearchSuggestionsAttributesTransfer;

class CatalogSearchSuggestionsResourceMapper implements CatalogSearchSuggestionsResourceMapperInterface
{
    /**
     * @var string
     */
    protected const SEARCH_RESPONSE_COMPLETION_KEY = 'completion';

    /**
     * @var string
     */
    protected const SEARCH_RESPONSE_SUGGESTION_BY_TYPE_KEY = 'suggestionByType';

    /**
     * @var string
     */
    protected const SEARCH_RESPONSE_PRODUCT_ABSTRACT_KEY = 'product_abstract';

    /**
     * @var string
     */
    protected const SEARCH_RESPONSE_PRODUCT_ABSTRACT_IMAGES_KEY = 'images';

    /**
     * @var string
     */
    protected const SEARCH_RESPONSE_CATEGORY_KEY = 'category';

    /**
     * @var string
     */
    protected const SEARCH_RESPONSE_CMS_PAGE_KEY = 'cms_page';

    /**
     * @var string
     */
    protected const SEARCH_RESPONSE_NAME_KEY = 'name';

    /**
     * @var string
     */
    protected const SEARCH_RESPONSE_URL_KEY = 'url';

    /**
     * @var string
     */
    protected const SEARCH_RESPONSE_ID_CATEGORY_KEY = 'id_category';

    /**
     * @return array
     */
    public function getEmptySearchResponse(): array
    {
        return [
            static::SEARCH_RESPONSE_COMPLETION_KEY => [],
            static::SEARCH_RESPONSE_SUGGESTION_BY_TYPE_KEY => [
                static::SEARCH_RESPONSE_PRODUCT_ABSTRACT_KEY => [],
                static::SEARCH_RESPONSE_CATEGORY_KEY => [],
                static::SEARCH_RESPONSE_CMS_PAGE_KEY => [],
            ],
        ];
    }

    /**
     * @param array $restSearchResponse
     *
     * @return \Generated\Shared\Transfer\RestCatalogSearchSuggestionsAttributesTransfer
     */
    public function mapSuggestionsToRestAttributesTransfer(array $restSearchResponse): RestCatalogSearchSuggestionsAttributesTransfer
    {
        $restSuggestionsAttributesTransfer = new RestCatalogSearchSuggestionsAttributesTransfer();
        $restSuggestionsAttributesTransfer->fromArray($restSearchResponse, true);

        $restSuggestionsAttributesTransfer = $this->mapCustomFields($restSuggestionsAttributesTransfer, $restSearchResponse);

        return $restSuggestionsAttributesTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\RestCatalogSearchSuggestionsAttributesTransfer $restSuggestionsAttributesTransfer
     * @param array $restSearchResponse
     *
     * @return \Generated\Shared\Transfer\RestCatalogSearchSuggestionsAttributesTransfer
     */
    protected function mapCustomFields(
        RestCatalogSearchSuggestionsAttributesTransfer $restSuggestionsAttributesTransfer,
        array $restSearchResponse
    ): RestCatalogSearchSuggestionsAttributesTransfer {
        $restSuggestionsAttributesTransfer = $this->mapProductSuggestions($restSuggestionsAttributesTransfer, $restSearchResponse);
        $restSuggestionsAttributesTransfer = $this->mapCategorySuggestions($restSuggestionsAttributesTransfer, $restSearchResponse);
        $restSuggestionsAttributesTransfer = $this->mapCmsPageSuggestions($restSuggestionsAttributesTransfer, $restSearchResponse);

        return $restSuggestionsAttributesTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\RestCatalogSearchSuggestionsAttributesTransfer $restSearchSuggestionsAttributesTransfer
     * @param array $restSearchResponse
     *
     * @return \Generated\Shared\Transfer\RestCatalogSearchSuggestionsAttributesTransfer
     */
    protected function mapProductSuggestions(
        RestCatalogSearchSuggestionsAttributesTransfer $restSearchSuggestionsAttributesTransfer,
        array $restSearchResponse
    ): RestCatalogSearchSuggestionsAttributesTransfer {
        return $this->mapSearchSuggestionProductsToRestCatalogSearchSuggestionsAttributesTransfer(
            $restSearchResponse,
            $restSearchSuggestionsAttributesTransfer,
        );
    }

    /**
     * @param \Generated\Shared\Transfer\RestCatalogSearchSuggestionsAttributesTransfer $restSearchSuggestionsAttributesTransfer
     * @param array $restSearchResponse
     *
     * @return \Generated\Shared\Transfer\RestCatalogSearchSuggestionsAttributesTransfer
     */
    protected function mapCategorySuggestions(
        RestCatalogSearchSuggestionsAttributesTransfer $restSearchSuggestionsAttributesTransfer,
        array $restSearchResponse
    ): RestCatalogSearchSuggestionsAttributesTransfer {
        $suggestionName = static::SEARCH_RESPONSE_CATEGORY_KEY;
        $suggestionKeysRequired = [static::SEARCH_RESPONSE_NAME_KEY, static::SEARCH_RESPONSE_URL_KEY];
        $categoriesSuggestions = $this->mapSuggestions($restSearchResponse, $suggestionName, $suggestionKeysRequired);
        $restSearchSuggestionsAttributesTransfer->setCategories($categoriesSuggestions);

        $suggestionCollectionKeysRequired = [static::SEARCH_RESPONSE_NAME_KEY, static::SEARCH_RESPONSE_URL_KEY, static::SEARCH_RESPONSE_ID_CATEGORY_KEY];
        $categoriesSuggestionCollection = $this->mapSuggestions($restSearchResponse, $suggestionName, $suggestionCollectionKeysRequired);

        foreach ($categoriesSuggestionCollection as $categoriesSuggestion) {
            $restSearchSuggestionsAttributesTransfer->addCategory(
                (new RestCatalogSearchSuggestionCategoriesTransfer())->fromArray($categoriesSuggestion, true),
            );
        }

        return $restSearchSuggestionsAttributesTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\RestCatalogSearchSuggestionsAttributesTransfer $restSearchSuggestionsAttributesTransfer
     * @param array $restSearchResponse
     *
     * @return \Generated\Shared\Transfer\RestCatalogSearchSuggestionsAttributesTransfer
     */
    protected function mapCmsPageSuggestions(
        RestCatalogSearchSuggestionsAttributesTransfer $restSearchSuggestionsAttributesTransfer,
        array $restSearchResponse
    ): RestCatalogSearchSuggestionsAttributesTransfer {
        $suggestionName = static::SEARCH_RESPONSE_CMS_PAGE_KEY;
        $suggestionKeysRequired = [static::SEARCH_RESPONSE_NAME_KEY, static::SEARCH_RESPONSE_URL_KEY];
        $cmsPagesSuggestions = $this->mapSuggestions($restSearchResponse, $suggestionName, $suggestionKeysRequired);
        $restSearchSuggestionsAttributesTransfer->setCmsPages($cmsPagesSuggestions);

        foreach ($cmsPagesSuggestions as $cmsPagesSuggestion) {
            $restSearchSuggestionsAttributesTransfer->addCmsPage(
                (new RestCatalogSearchSuggestionCmsPagesTransfer())->fromArray($cmsPagesSuggestion, true),
            );
        }

        return $restSearchSuggestionsAttributesTransfer;
    }

    /**
     * @param array $restSearchResponse
     * @param string $suggestionName
     * @param array $suggestionKeysRequired
     *
     * @return array
     */
    protected function mapSuggestions(array $restSearchResponse, string $suggestionName, array $suggestionKeysRequired): array
    {
        $result = [];

        if (!$this->checkSuggestionByTypeValues($restSearchResponse, $suggestionName)) {
            return $result;
        }

        $result = $this->mapArrayValuesByKeys(
            $restSearchResponse[static::SEARCH_RESPONSE_SUGGESTION_BY_TYPE_KEY][$suggestionName],
            $suggestionKeysRequired,
        );

        return $result;
    }

    /**
     * @param array $restSearchResponse
     * @param \Generated\Shared\Transfer\RestCatalogSearchSuggestionsAttributesTransfer $restSearchSuggestionsAttributesTransfer
     *
     * @return \Generated\Shared\Transfer\RestCatalogSearchSuggestionsAttributesTransfer
     */
    protected function mapSearchSuggestionProductsToRestCatalogSearchSuggestionsAttributesTransfer(
        array $restSearchResponse,
        RestCatalogSearchSuggestionsAttributesTransfer $restSearchSuggestionsAttributesTransfer
    ): RestCatalogSearchSuggestionsAttributesTransfer {
        if (!$this->checkSuggestionByTypeValues($restSearchResponse, static::SEARCH_RESPONSE_PRODUCT_ABSTRACT_KEY)) {
            return $restSearchSuggestionsAttributesTransfer;
        }

        $restSearchResponseSuggest = $restSearchResponse[static::SEARCH_RESPONSE_SUGGESTION_BY_TYPE_KEY];
        $restSearchResponseSuggestProducts = $restSearchResponseSuggest[static::SEARCH_RESPONSE_PRODUCT_ABSTRACT_KEY];

        foreach ($restSearchResponseSuggestProducts as $restSearchResponseSuggestProduct) {
            $restCatalogSearchSuggestionAbstractProducts = new RestCatalogSearchSuggestionAbstractProductsTransfer();
            $restCatalogSearchSuggestionAbstractProducts->fromArray(
                $restSearchResponseSuggestProduct,
                true,
            );

            $restSearchSuggestionsAttributesTransfer->addAbstractProduct($restCatalogSearchSuggestionAbstractProducts);
        }

        return $restSearchSuggestionsAttributesTransfer;
    }

    /**
     * @param array $source
     * @param array $keysRequired
     *
     * @return array
     */
    protected function mapArrayValuesByKeys(array $source, array $keysRequired): array
    {
        $result = [];
        foreach ($source as $data) {
            if ($this->checkSuggestionsValuesExists($data, $keysRequired)) {
                $result[] = array_intersect_key($data, array_flip($keysRequired));
            }
        }

        return $result;
    }

    /**
     * @param array $restSearchResponse
     * @param string $checkKey
     *
     * @return bool
     */
    protected function checkSuggestionByTypeValues(array $restSearchResponse, string $checkKey): bool
    {
        return isset($restSearchResponse[static::SEARCH_RESPONSE_SUGGESTION_BY_TYPE_KEY][$checkKey])
            && is_array($restSearchResponse[static::SEARCH_RESPONSE_SUGGESTION_BY_TYPE_KEY][$checkKey]);
    }

    /**
     * @param array $suggestions
     * @param array $keys
     *
     * @return bool
     */
    protected function checkSuggestionsValuesExists(array $suggestions, array $keys): bool
    {
        return !array_diff_key(array_flip($keys), $suggestions);
    }
}
