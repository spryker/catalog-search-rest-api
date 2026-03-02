<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CatalogSearchRestApi;

use Spryker\Glue\CatalogSearchRestApi\Dependency\Client\CatalogSearchRestApiToCatalogClientInterface;
use Spryker\Glue\CatalogSearchRestApi\Dependency\Client\CatalogSearchRestApiToCurrencyClientInterface;
use Spryker\Glue\CatalogSearchRestApi\Dependency\Client\CatalogSearchRestApiToGlossaryStorageClientInterface;
use Spryker\Glue\CatalogSearchRestApi\Dependency\Client\CatalogSearchRestApiToPriceClientInterface;
use Spryker\Glue\CatalogSearchRestApi\Processor\Catalog\CatalogSearchReader;
use Spryker\Glue\CatalogSearchRestApi\Processor\Catalog\CatalogSearchReaderInterface;
use Spryker\Glue\CatalogSearchRestApi\Processor\Catalog\CatalogSearchRequestParametersIntegerValidator;
use Spryker\Glue\CatalogSearchRestApi\Processor\Catalog\CatalogSearchRequestParametersIntegerValidatorInterface;
use Spryker\Glue\CatalogSearchRestApi\Processor\Mapper\CatalogSearchResourceMapper;
use Spryker\Glue\CatalogSearchRestApi\Processor\Mapper\CatalogSearchResourceMapperInterface;
use Spryker\Glue\CatalogSearchRestApi\Processor\Mapper\CatalogSearchSuggestionsResourceMapper;
use Spryker\Glue\CatalogSearchRestApi\Processor\Mapper\CatalogSearchSuggestionsResourceMapperInterface;
use Spryker\Glue\CatalogSearchRestApi\Processor\Translation\CatalogSearchTranslationExpander;
use Spryker\Glue\CatalogSearchRestApi\Processor\Translation\CatalogSearchTranslationExpanderInterface;
use Spryker\Glue\Kernel\AbstractFactory;

/**
 * @method \Spryker\Glue\CatalogSearchRestApi\CatalogSearchRestApiConfig getConfig()
 */
class CatalogSearchRestApiFactory extends AbstractFactory
{
    public function createCatalogSearchResourceMapper(): CatalogSearchResourceMapperInterface
    {
        return new CatalogSearchResourceMapper(
            $this->getCurrencyClient(),
        );
    }

    public function createCatalogSearchSuggestionsResourceMapper(): CatalogSearchSuggestionsResourceMapperInterface
    {
        return new CatalogSearchSuggestionsResourceMapper();
    }

    public function createCatalogSearchReader(): CatalogSearchReaderInterface
    {
        return new CatalogSearchReader(
            $this->getCatalogClient(),
            $this->getPriceClient(),
            $this->getCurrencyClient(),
            $this->getResourceBuilder(),
            $this->createCatalogSearchResourceMapper(),
            $this->createCatalogSearchSuggestionsResourceMapper(),
            $this->createCatalogSearchTranslationExpander(),
        );
    }

    public function createCatalogSearchTranslationExpander(): CatalogSearchTranslationExpanderInterface
    {
        return new CatalogSearchTranslationExpander($this->getGlossaryStorageClient());
    }

    public function createCatalogSearchRequestParametersIntegerValidator(): CatalogSearchRequestParametersIntegerValidatorInterface
    {
        return new CatalogSearchRequestParametersIntegerValidator(
            $this->getResourceBuilder(),
            $this->getConfig(),
        );
    }

    public function getCatalogClient(): CatalogSearchRestApiToCatalogClientInterface
    {
        return $this->getProvidedDependency(CatalogSearchRestApiDependencyProvider::CLIENT_CATALOG);
    }

    protected function getPriceClient(): CatalogSearchRestApiToPriceClientInterface
    {
        return $this->getProvidedDependency(CatalogSearchRestApiDependencyProvider::CLIENT_PRICE);
    }

    public function getGlossaryStorageClient(): CatalogSearchRestApiToGlossaryStorageClientInterface
    {
        return $this->getProvidedDependency(CatalogSearchRestApiDependencyProvider::CLIENT_GLOSSARY_STORAGE);
    }

    protected function getCurrencyClient(): CatalogSearchRestApiToCurrencyClientInterface
    {
        return $this->getProvidedDependency(CatalogSearchRestApiDependencyProvider::CLIENT_CURRENCY);
    }
}
