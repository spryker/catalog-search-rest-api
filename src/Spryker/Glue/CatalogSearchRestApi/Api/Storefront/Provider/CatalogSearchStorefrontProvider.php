<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CatalogSearchRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\CatalogSearchStorefrontResource;
use Generated\Shared\Transfer\PriceModeConfigurationTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\Catalog\CatalogClientInterface;
use Spryker\Client\Currency\CurrencyClientInterface;
use Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface;
use Spryker\Client\Price\PriceClientInterface;
use Spryker\Glue\CatalogSearchRestApi\Api\Storefront\Mapper\CatalogSearchResourceMapperInterface;
use Spryker\Glue\CatalogSearchRestApi\CatalogSearchRestApiConfig;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CatalogSearchStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string QUERY_STRING_PARAMETER = 'q';

    protected const string PARAMETER_NAME_CURRENCY = 'currency';

    protected const int DEFAULT_ITEMS_PER_PAGE = 12;

    protected const string GLOSSARY_SORT_PARAM_NAME_KEY_PREFIX = 'catalog.sort.';

    protected const string GLOSSARY_FACET_NAME_KEY_PREFIX = 'product.filter.';

    public function __construct(
        protected CatalogClientInterface $catalogClient,
        protected CurrencyClientInterface $currencyClient,
        protected PriceClientInterface $priceClient,
        protected GlossaryStorageClientInterface $glossaryStorageClient,
        protected CatalogSearchRestApiConfig $catalogSearchRestApiConfig,
        protected SerializerServiceInterface $serializer,
        protected CatalogSearchResourceMapperInterface $catalogSearchResourceMapper,
    ) {
    }

    /**
     * @return array<\Generated\Api\Storefront\CatalogSearchStorefrontResource>|null
     */
    protected function provideCollection(): array|null
    {
        $request = $this->getRequest();
        $this->validateIntegerRequestParameters($request->query->all());

        $currencyIsoCode = $request->query->get(static::PARAMETER_NAME_CURRENCY);
        $this->setGlobalCurrencyContext(is_string($currencyIsoCode) ? $currencyIsoCode : null);

        $searchString = (string)$request->query->get(static::QUERY_STRING_PARAMETER);
        $requestParameters = $request->query->all();

        if (is_array($requestParameters[static::QUERY_PARAMETER_PAGE] ?? null)) {
            $requestParameters = array_merge(
                $requestParameters,
                $this->buildSearchPaginationRequestParams(static::DEFAULT_ITEMS_PER_PAGE),
            );
        }

        $searchResult = $this->catalogClient->catalogSearch($searchString, $requestParameters);
        $locale = $this->getLocale()->getLocaleNameOrFail();

        $currencyTransfer = $this->currencyClient->getCurrent();
        $priceModeConfigurationTransfer = $this->resolvePriceModeConfiguration();

        $resourceData = $this->catalogSearchResourceMapper->mapSearchResultToResourceData(
            $searchResult,
            $currencyTransfer,
            $priceModeConfigurationTransfer,
        );

        $resource = $this->serializer->denormalize($resourceData, CatalogSearchStorefrontResource::class);

        $this->addTranslations($resource, $locale);

        return [$resource];
    }

    protected function setGlobalCurrencyContext(?string $currencyIsoCode): void
    {
        if (!$currencyIsoCode || !in_array($currencyIsoCode, $this->currencyClient->getCurrencyIsoCodes())) {
            return;
        }

        $this->currencyClient->setCurrentCurrencyIsoCode($currencyIsoCode);
    }

    /**
     * @param array<string, mixed> $requestParameters
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function validateIntegerRequestParameters(array $requestParameters): void
    {
        foreach ($this->catalogSearchRestApiConfig->getIntegerRequestParameterNames() as $parameterName) {
            $value = $this->getArrayElementByDotNotation($parameterName, $requestParameters);

            if (!$this->isValidIntegerParameter($value)) {
                throw new HttpException(
                    Response::HTTP_BAD_REQUEST,
                    sprintf(CatalogSearchRestApiConfig::ERROR_MESSAGE_PARAMETER_MUST_BE_INTEGER, $parameterName),
                );
            }
        }
    }

    protected function isValidIntegerParameter(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if ($value === '') {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function getArrayElementByDotNotation(string $key, array $data): mixed
    {
        if (strpos($key, '.') === false) {
            return $data[$key] ?? null;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($data) || !isset($data[$segment])) {
                return null;
            }

            $data = $data[$segment];
        }

        return $data;
    }

    protected function resolvePriceModeConfiguration(): PriceModeConfigurationTransfer
    {
        return (new PriceModeConfigurationTransfer())
            ->setCurrentPriceMode($this->priceClient->getCurrentPriceMode())
            ->setGrossModeIdentifier($this->priceClient->getGrossPriceModeIdentifier())
            ->setNetModeIdentifier($this->priceClient->getNetPriceModeIdentifier());
    }

    protected function addTranslations(CatalogSearchStorefrontResource $resource, string $locale): void
    {
        $this->addSortParamTranslations($resource, $locale);
        $this->addFacetNameTranslations($resource, $locale);
    }

    protected function addSortParamTranslations(CatalogSearchStorefrontResource $resource, string $locale): void
    {
        if ($resource->sort === [] || !isset($resource->sort['sortParamNames'])) {
            return;
        }

        $sortParamLocalizedNames = [];

        foreach ($resource->sort['sortParamNames'] as $sortParamName) {
            $sortParamLocalizedNames[$sortParamName] = $this->glossaryStorageClient
                ->translate(static::GLOSSARY_SORT_PARAM_NAME_KEY_PREFIX . $sortParamName, $locale);
        }

        $resource->sort['sortParamLocalizedNames'] = $sortParamLocalizedNames;
    }

    protected function addFacetNameTranslations(CatalogSearchStorefrontResource $resource, string $locale): void
    {
        $allFacets = array_merge($resource->valueFacets, $resource->rangeFacets);

        if ($allFacets === []) {
            return;
        }

        $glossaryKeys = array_map(
            fn (array $facet): string => strtolower(static::GLOSSARY_FACET_NAME_KEY_PREFIX . ($facet['name'] ?? '')),
            $allFacets,
        );

        $translations = $this->glossaryStorageClient->translateBulk($glossaryKeys, $locale);

        $resource->valueFacets = $this->applyFacetTranslations($resource->valueFacets, $translations);
        $resource->rangeFacets = $this->applyFacetTranslations($resource->rangeFacets, $translations);
    }

    /**
     * @param array<int, array<string, mixed>> $facets
     * @param array<string, string> $translations
     *
     * @return array<int, array<string, mixed>>
     */
    protected function applyFacetTranslations(array $facets, array $translations): array
    {
        return array_map(
            fn (array $facet): array => $facet + [
                'localizedName' => $translations[strtolower(static::GLOSSARY_FACET_NAME_KEY_PREFIX . ($facet['name'] ?? ''))] ?? '',
            ],
            $facets,
        );
    }
}
