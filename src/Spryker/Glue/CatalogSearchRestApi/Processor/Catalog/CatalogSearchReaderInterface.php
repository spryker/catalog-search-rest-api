<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CatalogSearchRestApi\Processor\Catalog;

use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface CatalogSearchReaderInterface
{
    public function catalogSearch(RestRequestInterface $restRequest): RestResponseInterface;

    public function catalogSuggestionsSearch(RestRequestInterface $restRequest): RestResponseInterface;
}
