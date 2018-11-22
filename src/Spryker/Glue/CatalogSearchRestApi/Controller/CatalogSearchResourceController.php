<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CatalogSearchRestApi\Controller;

use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\Kernel\Controller\AbstractController;

/**
 * @method \Spryker\Glue\CatalogSearchRestApi\CatalogSearchRestApiFactory getFactory()
 */
class CatalogSearchResourceController extends AbstractController
{
    /**
     * @Glue({
     *     "getCollection": {
     *          "summary": [
     *              "Catalog search."
     *          ],
     *          "parameters": [
     *              {
     *                  "name": "Accept-Language",
     *                  "in": "header"
     *              },
     *              {
     *                  "name": "q",
     *                  "in": "query",
     *                  "description": "Search query string.",
     *                  "required": true
     *              },
     *              {
     *                  "name": "currency",
     *                  "in": "query",
     *                  "description": "Currency code to process request with."
     *              },
     *              {
     *                  "name": "priceMode",
     *                  "in": "query",
     *                  "description": "Price mode to process request with."
     *              }
     *          ],
     *          "responses": {
     *              "400": "Invalid currency or price mode."
     *          }
     *     }
     * })
     *
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function getAction(RestRequestInterface $restRequest): RestResponseInterface
    {
        return $this->getFactory()
            ->createCatalogSearchReader()
            ->catalogSearch($restRequest);
    }
}
