<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CatalogSearchRestApi;

use Spryker\Glue\CatalogSearchRestApi\Dependency\Client\CatalogSearchRestApiToCatalogClientBridge;
use Spryker\Glue\CatalogSearchRestApi\Dependency\Client\CatalogSearchRestApiToCurrencyClientBridge;
use Spryker\Glue\CatalogSearchRestApi\Dependency\Client\CatalogSearchRestApiToGlossaryStorageClientBridge;
use Spryker\Glue\CatalogSearchRestApi\Dependency\Client\CatalogSearchRestApiToPriceClientBridge;
use Spryker\Glue\Kernel\AbstractBundleDependencyProvider;
use Spryker\Glue\Kernel\Container;

/**
 * @method \Spryker\Glue\CatalogSearchRestApi\CatalogSearchRestApiConfig getConfig()
 */
class CatalogSearchRestApiDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const CLIENT_CATALOG = 'CLIENT_CATALOG';

    /**
     * @var string
     */
    public const CLIENT_PRICE = 'CLIENT_PRICE';

    /**
     * @var string
     */
    public const CLIENT_CURRENCY = 'CLIENT_CURRENCY';

    /**
     * @var string
     */
    public const CLIENT_GLOSSARY_STORAGE = 'CLIENT_GLOSSARY_STORAGE';

    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);

        $container = $this->addGlossaryStorageClient($container);
        $container = $this->addCatalogClient($container);
        $container = $this->addPriceClient($container);
        $container = $this->addCurrencyClient($container);

        return $container;
    }

    protected function addCatalogClient(Container $container): Container
    {
        $container->set(static::CLIENT_CATALOG, function (Container $container) {
            return new CatalogSearchRestApiToCatalogClientBridge($container->getLocator()->catalog()->client());
        });

        return $container;
    }

    protected function addPriceClient(Container $container): Container
    {
        $container->set(static::CLIENT_PRICE, function (Container $container) {
            return new CatalogSearchRestApiToPriceClientBridge($container->getLocator()->price()->client());
        });

        return $container;
    }

    protected function addCurrencyClient(Container $container): Container
    {
        $container->set(static::CLIENT_CURRENCY, function (Container $container) {
            return new CatalogSearchRestApiToCurrencyClientBridge($container->getLocator()->currency()->client());
        });

        return $container;
    }

    protected function addGlossaryStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_GLOSSARY_STORAGE, function (Container $container) {
            return new CatalogSearchRestApiToGlossaryStorageClientBridge($container->getLocator()->glossaryStorage()->client());
        });

        return $container;
    }
}
