<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CatalogSearchRestApi\Processor\Translation;

use Generated\Shared\Transfer\RestCatalogSearchAttributesTransfer;
use Spryker\Glue\CatalogSearchRestApi\Dependency\Client\CatalogSearchRestApiToGlossaryStorageClientInterface;

class CatalogSearchTranslationExpander implements CatalogSearchTranslationExpanderInterface
{
    /**
     * @var string
     */
    protected const GLOSSARY_SORT_PARAM_NAME_KEY_PREFIX = 'catalog.sort.';

    /**
     * @var string
     */
    protected const GLOSSARY_FACET_NAME_KEY_PREFIX = 'product.filter.';

    /**
     * @var \Spryker\Glue\CatalogSearchRestApi\Dependency\Client\CatalogSearchRestApiToGlossaryStorageClientInterface
     */
    protected $glossaryStorageClient;

    public function __construct(CatalogSearchRestApiToGlossaryStorageClientInterface $glossaryStorageClient)
    {
        $this->glossaryStorageClient = $glossaryStorageClient;
    }

    public function addTranslations(
        RestCatalogSearchAttributesTransfer $restCatalogSearchAttributesTransfer,
        string $localName
    ): RestCatalogSearchAttributesTransfer {
        $restCatalogSearchAttributesTransfer = $this->addSortParamTranslation($restCatalogSearchAttributesTransfer, $localName);
        $restCatalogSearchAttributesTransfer = $this->addFacetNameTranslation($restCatalogSearchAttributesTransfer, $localName);

        return $restCatalogSearchAttributesTransfer;
    }

    protected function addSortParamTranslation(
        RestCatalogSearchAttributesTransfer $restCatalogSearchAttributesTransfer,
        string $localName
    ): RestCatalogSearchAttributesTransfer {
        $sortTransfer = $restCatalogSearchAttributesTransfer->getSort();
        if (!$sortTransfer) {
            return $restCatalogSearchAttributesTransfer;
        }

        $sortParamLocalizedNames = [];
        foreach ($sortTransfer->getSortParamNames() as $sortParamName) {
            $sortParamLocalizedNames[$sortParamName] = $this->glossaryStorageClient
                ->translate(static::GLOSSARY_SORT_PARAM_NAME_KEY_PREFIX . $sortParamName, $localName);
        }

        $restCatalogSearchAttributesTransfer->setSort($sortTransfer->setSortParamLocalizedNames($sortParamLocalizedNames));

        return $restCatalogSearchAttributesTransfer;
    }

    protected function addFacetNameTranslation(
        RestCatalogSearchAttributesTransfer $restCatalogSearchAttributesTransfer,
        string $localName
    ): RestCatalogSearchAttributesTransfer {
        foreach ($restCatalogSearchAttributesTransfer->getValueFacets() as $facet) {
            $glossaryKey = static::GLOSSARY_FACET_NAME_KEY_PREFIX . $facet->getName();
            $facet->setLocalizedName($this->glossaryStorageClient->translate($glossaryKey, $localName));
        }
        foreach ($restCatalogSearchAttributesTransfer->getRangeFacets() as $facet) {
            $glossaryKey = static::GLOSSARY_FACET_NAME_KEY_PREFIX . $facet->getName();
            $facet->setLocalizedName($this->glossaryStorageClient->translate($glossaryKey, $localName));
        }

        return $restCatalogSearchAttributesTransfer;
    }
}
