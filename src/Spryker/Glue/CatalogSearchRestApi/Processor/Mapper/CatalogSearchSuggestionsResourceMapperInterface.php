<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CatalogSearchRestApi\Processor\Mapper;

use Generated\Shared\Transfer\RestCatalogSearchSuggestionsAttributesTransfer;

interface CatalogSearchSuggestionsResourceMapperInterface
{
    public function mapSuggestionsToRestAttributesTransfer(array $restSearchResponse): RestCatalogSearchSuggestionsAttributesTransfer;

    public function getEmptySearchResponse(): array;
}
