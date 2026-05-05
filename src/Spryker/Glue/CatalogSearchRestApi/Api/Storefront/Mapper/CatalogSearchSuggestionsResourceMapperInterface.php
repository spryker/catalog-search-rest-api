<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CatalogSearchRestApi\Api\Storefront\Mapper;

interface CatalogSearchSuggestionsResourceMapperInterface
{
    /**
     * @param array<string, mixed> $suggestionsResult
     *
     * @return array<string, mixed>
     */
    public function mapSuggestionsResultToResourceData(array $suggestionsResult): array;
}
