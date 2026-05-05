<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CatalogSearchRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\CatalogSearchSuggestionsStorefrontResource;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\Catalog\CatalogClientInterface;
use Spryker\Client\Currency\CurrencyClientInterface;
use Spryker\Glue\CatalogSearchRestApi\Api\Storefront\Mapper\CatalogSearchSuggestionsResourceMapperInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;

class CatalogSearchSuggestionsStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string QUERY_STRING_PARAMETER = 'q';

    protected const string PARAMETER_NAME_CURRENCY = 'currency';

    protected const int DEFAULT_ITEMS_PER_PAGE = 10;

    public function __construct(
        protected CatalogClientInterface $catalogClient,
        protected CurrencyClientInterface $currencyClient,
        protected SerializerServiceInterface $serializer,
        protected CatalogSearchSuggestionsResourceMapperInterface $catalogSearchSuggestionsResourceMapper,
    ) {
    }

    /**
     * @return array<\Generated\Api\Storefront\CatalogSearchSuggestionsStorefrontResource>|null
     */
    protected function provideCollection(): array|null
    {
        $request = $this->getRequest();
        $currencyIsoCode = $request->query->get(static::PARAMETER_NAME_CURRENCY);
        $this->setGlobalCurrencyContext(is_string($currencyIsoCode) ? $currencyIsoCode : null);

        $searchString = (string)$request->query->get(static::QUERY_STRING_PARAMETER);

        if (!$searchString) {
            return $this->createEmptyResponse();
        }

        $requestParameters = $request->query->all();

        if (is_array($requestParameters[static::QUERY_PARAMETER_PAGE] ?? null)) {
            $requestParameters = array_merge(
                $requestParameters,
                $this->buildSearchPaginationRequestParams(static::DEFAULT_ITEMS_PER_PAGE),
            );
        }

        $suggestions = $this->catalogClient->catalogSuggestSearch($searchString, $requestParameters);

        $resource = $this->serializer->denormalize(
            $this->catalogSearchSuggestionsResourceMapper->mapSuggestionsResultToResourceData($suggestions),
            CatalogSearchSuggestionsStorefrontResource::class,
        );

        return [$resource];
    }

    /**
     * @return array<\Generated\Api\Storefront\CatalogSearchSuggestionsStorefrontResource>
     */
    protected function createEmptyResponse(): array
    {
        return [$this->serializer->denormalize(
            $this->catalogSearchSuggestionsResourceMapper->mapSuggestionsResultToResourceData([]),
            CatalogSearchSuggestionsStorefrontResource::class,
        )];
    }

    protected function setGlobalCurrencyContext(?string $currencyIsoCode): void
    {
        if (!$currencyIsoCode || !in_array($currencyIsoCode, $this->currencyClient->getCurrencyIsoCodes())) {
            return;
        }

        $this->currencyClient->setCurrentCurrencyIsoCode($currencyIsoCode);
    }
}
