<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CatalogSearchRestApi\Processor\Mapper;

use Generated\Shared\Transfer\FacetSearchResultTransfer;
use Generated\Shared\Transfer\RangeSearchResultTransfer;
use Generated\Shared\Transfer\RestCatalogSearchAttributesTransfer;
use Generated\Shared\Transfer\RestFacetSearchResultAttributesTransfer;
use Generated\Shared\Transfer\RestPricePriceModeConfigurationTransfer;
use Generated\Shared\Transfer\RestPriceProductAttributesTransfer;
use Generated\Shared\Transfer\RestRangeSearchResultAttributesTransfer;

class CatalogSearchResourceMapper implements CatalogSearchResourceMapperInterface
{
    protected const FACETS = 'facets';

    /**
     * @param array $restSearchResponse
     * @param string $currency
     *
     * @return \Generated\Shared\Transfer\RestCatalogSearchAttributesTransfer
     */
    public function mapSearchResponseAttributesTransferToRestResponse(array $restSearchResponse, string $currency): RestCatalogSearchAttributesTransfer
    {
        $restSearchAttributesTransfer = (new RestCatalogSearchAttributesTransfer())->fromArray($restSearchResponse, true);
        $restSearchAttributesTransfer->setCurrency($currency);
        if (isset($restSearchResponse[self::FACETS])) {
            $restSearchAttributesTransfer = $this->mapSearchResponseFacetTransfersToSearchAttributesTransfer(
                $restSearchResponse[self::FACETS],
                $restSearchAttributesTransfer
            );
        }

        return $restSearchAttributesTransfer;
    }

    /**
     * @param array $facets
     * @param \Generated\Shared\Transfer\RestCatalogSearchAttributesTransfer $restSearchAttributesTransfer
     *
     * @return \Generated\Shared\Transfer\RestCatalogSearchAttributesTransfer
     */
    protected function mapSearchResponseFacetTransfersToSearchAttributesTransfer(array $facets, RestCatalogSearchAttributesTransfer $restSearchAttributesTransfer): RestCatalogSearchAttributesTransfer
    {
        foreach ($facets as $facet) {
            if ($facet instanceof FacetSearchResultTransfer) {
                $restSearchAttributesTransfer->addValueFacet(
                    (new RestFacetSearchResultAttributesTransfer())->fromArray($facet->toArray(), true)
                );
                continue;
            }
            if ($facet instanceof RangeSearchResultTransfer) {
                $restSearchAttributesTransfer->addRangeFacet(
                    (new RestRangeSearchResultAttributesTransfer())->fromArray($facet->toArray(), true)
                );
            }
        }

        return $restSearchAttributesTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\RestCatalogSearchAttributesTransfer $restSearchAttributesTransfer
     * @param \Generated\Shared\Transfer\RestPricePriceModeConfigurationTransfer $priceModeInformation
     *
     * @return \Generated\Shared\Transfer\RestCatalogSearchAttributesTransfer
     */
    public function mapPrices(
        RestCatalogSearchAttributesTransfer $restSearchAttributesTransfer,
        RestPricePriceModeConfigurationTransfer $priceModeInformation
    ): RestCatalogSearchAttributesTransfer {
        foreach ($restSearchAttributesTransfer->getProducts() as $product) {
            $prices = [];
            foreach ($product->getPrices() as $priceType => $price) {
                $priceData = $this
                    ->getPriceTransfer($priceType, $price, $priceModeInformation)
                    ->modifiedToArray(true, true);

                $prices[] = $priceData + [$priceType => $price];
            }
            $product->setPrices($prices);
        }

        return $restSearchAttributesTransfer;
    }

    /**
     * @param string $priceType
     * @param int $price
     * @param \Generated\Shared\Transfer\RestPricePriceModeConfigurationTransfer $priceModeInformation
     *
     * @return \Generated\Shared\Transfer\RestPriceProductAttributesTransfer
     */
    protected function getPriceTransfer(
        string $priceType,
        int $price,
        RestPricePriceModeConfigurationTransfer $priceModeInformation
    ): RestPriceProductAttributesTransfer {
        $restPriceProductAttributes = new RestPriceProductAttributesTransfer();
        $restPriceProductAttributes->setPriceTypeName($priceType);
        if ($priceModeInformation->getCurrentPriceMode() === $priceModeInformation->getGrossModeIdentifier()) {
            return $restPriceProductAttributes->setGrossAmount($price);
        }

        if ($priceModeInformation->getCurrentPriceMode() === $priceModeInformation->getNetModeIdentifier()) {
            return $restPriceProductAttributes->setNetAmount($price);
        }

        return $restPriceProductAttributes;
    }
}
