<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CatalogSearchRestApi\Api\Storefront\Mapper;

use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\PriceModeConfigurationTransfer;

interface CatalogSearchResourceMapperInterface
{
    /**
     * @param array<string, mixed> $searchResult
     *
     * @return array<string, mixed>
     */
    public function mapSearchResultToResourceData(
        array $searchResult,
        CurrencyTransfer $currencyTransfer,
        PriceModeConfigurationTransfer $priceModeConfigurationTransfer,
    ): array;
}
