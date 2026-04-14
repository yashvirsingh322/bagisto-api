<?php

namespace Webkul\BagistoApi\Providers;

use ApiPlatform\GraphQl\Resolver\Factory\ResolverFactoryInterface;
use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use ApiPlatform\GraphQl\Serializer\SerializerContextBuilder as GraphQlSerializerContextBuilder;
use ApiPlatform\GraphQl\Type\Definition\IterableType;
use ApiPlatform\Laravel\Eloquent\State\CollectionProvider;
use ApiPlatform\Laravel\Eloquent\State\ItemProvider;
use ApiPlatform\Laravel\Eloquent\State\LinksHandler;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use ApiPlatform\Laravel\Eloquent\State\QueryExtensionInterface;
use ApiPlatform\Laravel\ServiceLocator;
use ApiPlatform\Metadata\IdentifiersExtractorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Webkul\BagistoApi\Console\Commands\ApiKeyMaintenanceCommand;
use Webkul\BagistoApi\Console\Commands\ApiKeyManagementCommand;
use Webkul\BagistoApi\Console\Commands\ClearApiPlatformCacheCommand;
use Webkul\BagistoApi\Console\Commands\GenerateStorefrontKey;
use Webkul\BagistoApi\Console\Commands\InstallApiPlatformCommand;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\GraphQl\Serializer\FixedSerializerContextBuilder;
use Webkul\BagistoApi\Http\Controllers\AdminGraphQLPlaygroundController;
use Webkul\BagistoApi\Http\Controllers\ApiEntrypointController;
use Webkul\BagistoApi\Http\Controllers\DownloadablePurchasedController;
use Webkul\BagistoApi\Http\Controllers\DownloadSampleController;
use Webkul\BagistoApi\Http\Controllers\GraphQLPlaygroundController;
use Webkul\BagistoApi\Http\Controllers\InvoicePdfController;
use Webkul\BagistoApi\Http\Controllers\SwaggerUIController;
use Webkul\BagistoApi\Http\Middleware\LogApiRequests;
use Webkul\BagistoApi\Http\Middleware\RateLimitApi;
use Webkul\BagistoApi\Http\Middleware\SecurityHeaders;
use Webkul\BagistoApi\Http\Middleware\SetLocaleChannel;
use Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey;
use Webkul\BagistoApi\Metadata\CustomIdentifiersExtractor;
use Webkul\BagistoApi\OpenApi\SplitOpenApiFactory;
use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\Resolver\CategoryCollectionResolver;
use Webkul\BagistoApi\Resolver\CustomerQueryResolver;
use Webkul\BagistoApi\Resolver\Factory\ProductRelationResolverFactory;
use Webkul\BagistoApi\Resolver\PageByUrlKeyResolver;
use Webkul\BagistoApi\Resolver\ProductCollectionResolver;
use Webkul\BagistoApi\Resolver\SingleProductBagistoApiResolver;
use Webkul\BagistoApi\Routing\CustomIriConverter;
use Webkul\BagistoApi\Serializer\TokenHeaderDenormalizer;
use Webkul\BagistoApi\Services\CartTokenService;
use Webkul\BagistoApi\Services\StorefrontKeyService;
use Webkul\BagistoApi\Services\TokenHeaderService;
use Webkul\BagistoApi\State\AttributeCollectionProvider;
use Webkul\BagistoApi\State\AttributeOptionCollectionProvider;
use Webkul\BagistoApi\State\AttributeOptionQueryProvider;
use Webkul\BagistoApi\State\AttributeValueProcessor;
use Webkul\BagistoApi\State\AuthenticatedCustomerProvider;
use Webkul\BagistoApi\State\BookingSlotProvider;
use Webkul\BagistoApi\State\BundleOptionProductsProvider;
use Webkul\BagistoApi\State\CancelOrderProcessor;
use Webkul\BagistoApi\State\CartTokenMutationProvider;
use Webkul\BagistoApi\State\CartTokenProcessor;
use Webkul\BagistoApi\State\CategoryTreeProvider;
use Webkul\BagistoApi\State\ChannelProvider;
use Webkul\BagistoApi\State\CheckoutAddressProvider;
use Webkul\BagistoApi\State\CheckoutProcessor;
use Webkul\BagistoApi\State\CompareItemProcessor;
use Webkul\BagistoApi\State\CompareItemProvider;
use Webkul\BagistoApi\State\CountryStateCollectionProvider;
use Webkul\BagistoApi\State\CountryStateQueryProvider;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;
use Webkul\BagistoApi\State\CustomerAddressProvider;
use Webkul\BagistoApi\State\CustomerAddressTokenProcessor;
use Webkul\BagistoApi\State\CustomerDownloadableProductProvider;
use Webkul\BagistoApi\State\CustomerInvoiceProvider;
use Webkul\BagistoApi\State\CustomerOrderProvider;
use Webkul\BagistoApi\State\CustomerOrderShipmentProvider;
use Webkul\BagistoApi\State\CustomerProcessor;
use Webkul\BagistoApi\State\CustomerProfileProcessor;
use Webkul\BagistoApi\State\CustomerReviewProvider;
use Webkul\BagistoApi\State\DefaultChannelProvider;
use Webkul\BagistoApi\State\DeleteAllCompareItemsProcessor;
use Webkul\BagistoApi\State\DeleteAllWishlistsProcessor;
use Webkul\BagistoApi\State\DownloadableLinksProvider;
use Webkul\BagistoApi\State\DownloadableProductProcessor;
use Webkul\BagistoApi\State\DownloadableSamplesProvider;
use Webkul\BagistoApi\State\FilterableAttributesProvider;
use Webkul\BagistoApi\State\ForgotPasswordProcessor;
use Webkul\BagistoApi\State\GetCheckoutAddressCollectionProvider;
use Webkul\BagistoApi\State\GroupedProductsProvider;
use Webkul\BagistoApi\State\LoginProcessor;
use Webkul\BagistoApi\State\LogoutProcessor;
use Webkul\BagistoApi\State\MoveWishlistToCartProcessor;
use Webkul\BagistoApi\State\PageProvider;
use Webkul\BagistoApi\State\PaymentMethodsProvider;
use Webkul\BagistoApi\State\Processor\ContactUsProcessor;
use Webkul\BagistoApi\State\Processor\NewsletterSubscriptionProcessor;
use Webkul\BagistoApi\State\ProductBagistoApiProvider;
use Webkul\BagistoApi\State\ProductCustomerGroupPriceProcessor;
use Webkul\BagistoApi\State\ProductCustomerGroupPriceProvider;
use Webkul\BagistoApi\State\ProductGraphQLProvider;
use Webkul\BagistoApi\State\ProductProcessor;
use Webkul\BagistoApi\State\ProductRelationProvider;
use Webkul\BagistoApi\State\ProductReviewProcessor;
use Webkul\BagistoApi\State\ProductReviewProvider;
use Webkul\BagistoApi\State\ReorderProcessor;
use Webkul\BagistoApi\State\ShippingRatesProvider;
use Webkul\BagistoApi\State\SnakeCaseLinksHandler;
use Webkul\BagistoApi\State\VerifyTokenProcessor;
use Webkul\BagistoApi\State\WishlistProcessor;
use Webkul\BagistoApi\State\WishlistProvider;

class BagistoApiServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider bindings.
     */
    public function register(): void
    {
        $this->registerSnakeCaseLinksHandlerFix();

        $this->app->singleton(IterableType::class);
        $this->app->tag(IterableType::class, 'api_platform.graphql.type');

        $this->app->singleton(StorefrontKeyService::class, function ($app) {
            return new StorefrontKeyService;
        });

        $this->app->extend(OpenApiFactoryInterface::class, function ($openApiFactory) {
            return new SplitOpenApiFactory($openApiFactory);
        });

        $this->app->singleton(TokenHeaderDenormalizer::class);

        $this->app->singleton('token-header-service', function ($app) {
            return new TokenHeaderService;
        });

        $this->app->alias('token-header-service', 'Webkul\BagistoApi\Services\TokenHeaderService');

        $this->app->singleton('cart-token-service', function ($app) {
            return new CartTokenService(
                $app->make('Webkul\Checkout\Repositories\CartRepository'),
                $app->make('Webkul\BagistoApi\Repositories\GuestCartTokensRepository'),
                $app->make('Webkul\Customer\Repositories\CustomerRepository')
            );
        });

        $this->app->alias('cart-token-service', CartTokenFacade::class);

        $this->app->singleton('Webkul\BagistoApi\Repositories\GuestCartTokensRepository', function ($app) {
            return new GuestCartTokensRepository($app);
        });

        $this->app->tag(ProductProcessor::class, ProcessorInterface::class);
        $this->app->tag(AttributeValueProcessor::class, ProcessorInterface::class);
        $this->app->tag(ProductCustomerGroupPriceProcessor::class, ProcessorInterface::class);
        $this->app->tag(CustomerProcessor::class, ProcessorInterface::class);
        $this->app->tag(LoginProcessor::class, ProcessorInterface::class);
        $this->app->tag(VerifyTokenProcessor::class, ProcessorInterface::class);
        $this->app->tag(LogoutProcessor::class, ProcessorInterface::class);
        $this->app->tag(ForgotPasswordProcessor::class, ProcessorInterface::class);
        $this->app->tag(CustomerProfileProcessor::class, ProcessorInterface::class);
        $this->app->tag(CustomerAddressTokenProcessor::class, ProcessorInterface::class);
        $this->app->tag(CartTokenProcessor::class, ProcessorInterface::class);
        $this->app->tag(CheckoutProcessor::class, ProcessorInterface::class);
        $this->app->tag(ProductReviewProcessor::class, ProcessorInterface::class);
        $this->app->tag(CompareItemProcessor::class, ProcessorInterface::class);
        $this->app->tag(DownloadableProductProcessor::class, ProcessorInterface::class);
        $this->app->tag(NewsletterSubscriptionProcessor::class, ProcessorInterface::class);
        $this->app->tag(WishlistProcessor::class, ProcessorInterface::class);
        $this->app->tag(MoveWishlistToCartProcessor::class, ProcessorInterface::class);
        $this->app->tag(DeleteAllWishlistsProcessor::class, ProcessorInterface::class);
        $this->app->tag(DeleteAllCompareItemsProcessor::class, ProcessorInterface::class);
        $this->app->tag(CancelOrderProcessor::class, ProcessorInterface::class);
        $this->app->tag(ReorderProcessor::class, ProcessorInterface::class);
        $this->app->tag(ContactUsProcessor::class, ProcessorInterface::class);

        $this->app->tag(TokenHeaderDenormalizer::class, 'serializer.normalizer');

        $this->app->singleton(ProductCustomerGroupPriceProcessor::class, function ($app) {
            return new ProductCustomerGroupPriceProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(CustomerProcessor::class, function ($app) {
            return new CustomerProcessor(
                $app->make('Webkul\Customer\Repositories\CustomerRepository'),
                $app->make('Webkul\BagistoApi\Validators\CustomerValidator')
            );
        });

        $this->app->singleton(LoginProcessor::class, function ($app) {
            return new LoginProcessor(
                $app->make('Webkul\BagistoApi\Validators\LoginValidator')
            );
        });

        $this->app->singleton(CustomerProfileProcessor::class, function ($app) {
            return new CustomerProfileProcessor(
                $app->make('Webkul\BagistoApi\Validators\CustomerValidator')
            );
        });

        $this->app->singleton(CartTokenProcessor::class, function ($app) {
            return new CartTokenProcessor(
                $app->make('Webkul\Checkout\Repositories\CartRepository'),
                $app->make('Webkul\BagistoApi\Repositories\GuestCartTokensRepository')
            );
        });

        $this->app->singleton(CheckoutProcessor::class, function ($app) {
            return new CheckoutProcessor(
                $app->make('Webkul\Customer\Repositories\CustomerRepository'),
                $app->make('Webkul\Sales\Repositories\OrderRepository'),
                $app->make('Webkul\Checkout\Repositories\CartRepository')
            );
        });

        $this->app->singleton(ProductReviewProcessor::class, function ($app) {
            return new ProductReviewProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(CompareItemProcessor::class, function ($app) {
            return new CompareItemProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(WishlistProcessor::class, function ($app) {
            return new WishlistProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(MoveWishlistToCartProcessor::class, function ($app) {
            return new MoveWishlistToCartProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(DeleteAllWishlistsProcessor::class, function ($app) {
            return new DeleteAllWishlistsProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(DeleteAllCompareItemsProcessor::class, function ($app) {
            return new DeleteAllCompareItemsProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(CancelOrderProcessor::class, function ($app) {
            return new CancelOrderProcessor(
                $app->make(PersistProcessor::class),
                $app->make('Webkul\Sales\Repositories\OrderRepository')
            );
        });

        $this->app->singleton(ReorderProcessor::class, function ($app) {
            return new ReorderProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(LogoutProcessor::class, function ($app) {
            return new LogoutProcessor;
        });

        $this->app->tag(CheckoutAddressProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerAddressProvider::class, ProviderInterface::class);
        $this->app->tag(GetCheckoutAddressCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(PaymentMethodsProvider::class, ProviderInterface::class);
        $this->app->tag(ShippingRatesProvider::class, ProviderInterface::class);
        $this->app->tag(AuthenticatedCustomerProvider::class, ProviderInterface::class);
        $this->app->tag(CartTokenMutationProvider::class, ProviderInterface::class);
        $this->app->tag(ChannelProvider::class, ProviderInterface::class);
        $this->app->tag(DefaultChannelProvider::class, ProviderInterface::class);
        $this->app->tag(ProductBagistoApiProvider::class, ProviderInterface::class);
        $this->app->tag(ProductGraphQLProvider::class, ProviderInterface::class);
        $this->app->tag(ProductCustomerGroupPriceProvider::class, ProviderInterface::class);
        $this->app->tag(ProductRelationProvider::class, ProviderInterface::class);
        $this->app->tag(BundleOptionProductsProvider::class, ProviderInterface::class);
        $this->app->tag(GroupedProductsProvider::class, ProviderInterface::class);
        $this->app->tag(DownloadableLinksProvider::class, ProviderInterface::class);
        $this->app->tag(DownloadableSamplesProvider::class, ProviderInterface::class);
        $this->app->tag(ProductReviewProvider::class, ProviderInterface::class);
        $this->app->tag(FilterableAttributesProvider::class, ProviderInterface::class);
        $this->app->tag(AttributeCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AttributeOptionCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AttributeOptionQueryProvider::class, ProviderInterface::class);
        $this->app->tag(CountryStateCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(CountryStateQueryProvider::class, ProviderInterface::class);
        $this->app->tag(CategoryTreeProvider::class, ProviderInterface::class);
        $this->app->tag(BookingSlotProvider::class, ProviderInterface::class);
        $this->app->tag(CursorAwareCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(PageProvider::class, ProviderInterface::class);
        $this->app->tag(WishlistProvider::class, ProviderInterface::class);
        $this->app->tag(CompareItemProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerReviewProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerOrderProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerDownloadableProductProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerInvoiceProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerOrderShipmentProvider::class, ProviderInterface::class);

        $this->app->singleton(GetCheckoutAddressCollectionProvider::class, function ($app) {
            return new GetCheckoutAddressCollectionProvider(
                $app->make('ApiPlatform\State\Pagination\Pagination')
            );
        });

        $this->app->singleton(WishlistProvider::class, function ($app) {
            return new WishlistProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CompareItemProvider::class, function ($app) {
            return new CompareItemProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerReviewProvider::class, function ($app) {
            return new CustomerReviewProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerOrderProvider::class, function ($app) {
            return new CustomerOrderProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerDownloadableProductProvider::class, function ($app) {
            return new CustomerDownloadableProductProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerInvoiceProvider::class, function ($app) {
            return new CustomerInvoiceProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerOrderShipmentProvider::class, function ($app) {
            return new CustomerOrderShipmentProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerAddressProvider::class, function ($app) {
            return new CustomerAddressProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(ProductBagistoApiProvider::class, function ($app) {
            return new ProductBagistoApiProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(ProductGraphQLProvider::class, function ($app) {
            return new ProductGraphQLProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(ProductRelationProvider::class, function ($app) {
            return new ProductRelationProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(ProductReviewProvider::class, function ($app) {
            return new ProductReviewProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(GroupedProductsProvider::class, function ($app) {
            return new GroupedProductsProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(DownloadableLinksProvider::class, function ($app) {
            return new DownloadableLinksProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(DownloadableSamplesProvider::class, function ($app) {
            return new DownloadableSamplesProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(FilterableAttributesProvider::class, function ($app) {
            return new FilterableAttributesProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(AttributeCollectionProvider::class, function ($app) {
            return new AttributeCollectionProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(AttributeOptionCollectionProvider::class, function ($app) {
            return new AttributeOptionCollectionProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CountryStateCollectionProvider::class, function ($app) {
            return new CountryStateCollectionProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(ProductCollectionResolver::class);
        $this->app->tag(SingleProductBagistoApiResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(CategoryCollectionResolver::class, QueryCollectionResolverInterface::class);
        $this->app->tag(BaseQueryItemResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(CustomerQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(PageByUrlKeyResolver::class, QueryCollectionResolverInterface::class);

        $this->app->extend(ResolverFactoryInterface::class, function ($resolverFactory, $app) {
            return new ProductRelationResolverFactory(
                $resolverFactory,
                $app->make(ProductRelationProvider::class)
            );
        });

        $this->app->extend(IdentifiersExtractorInterface::class, function ($extractor) {
            return new CustomIdentifiersExtractor($extractor);
        });

        $this->app->extend(IriConverterInterface::class, function ($converter, $app) {
            return new CustomIriConverter(
                $converter,
                $app->make(ResourceMetadataCollectionFactoryInterface::class)
            );
        });

        $this->app->extend(GraphQlSerializerContextBuilder::class, function ($builder, $app) {
            return new FixedSerializerContextBuilder(
                $builder,
                $app->make(NameConverterInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'bagistoapi');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'webkul');

        if ($this->isRunningAsVendorPackage()) {
            $this->publishes([
                __DIR__.'/../config/api-platform-vendor.php' => config_path('api-platform.php'),
            ], 'bagistoapi-config');
        } else {
            $this->publishes([
                __DIR__.'/../config/api-platform.php' => config_path('api-platform.php'),
            ], 'bagistoapi-config');
        }

        $this->publishes([
            __DIR__.'/../config/graphql-auth.php' => config_path('graphql-auth.php'),
            __DIR__.'/../config/storefront.php' => config_path('storefront.php'),
        ], 'bagistoapi-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/webkul'),
        ], 'bagistoapi-views');

        $this->publishes([
            __DIR__.'/../Resources/assets' => public_path('themes/admin/default/assets'),
        ], 'bagistoapi-assets');

        $this->runInstallationIfNeeded();
        $this->registerApiResources();
        $this->registerApiDocumentationRoutes();
        $this->registerMiddlewareAliases();
        $this->registerServiceProviders();

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register API documentation routes.
     */
    protected function registerApiDocumentationRoutes(): void
    {
        Route::get('/api', ApiEntrypointController::class)
            ->name('bagistoapi.docs-index');

        Route::get('/api/shop', [
            SwaggerUIController::class, 'shopApi',
        ])->name('bagistoapi.shop-docs')->where('_format', '^(?!json|xml|csv)');

        Route::get('/api/admin', [
            SwaggerUIController::class, 'adminApi',
        ])->name('bagistoapi.admin-docs')->where('_format', '^(?!json|xml|csv)');

        Route::get('/api/shop/docs', [
            SwaggerUIController::class, 'shopApiDocs',
        ])->name('bagistoapi.shop-api-spec');

        Route::get('/api/admin/docs', [
            SwaggerUIController::class, 'adminApiDocs',
        ])->name('bagistoapi.admin-api-spec');

        Route::get('/api/graphiql', GraphQLPlaygroundController::class)
            ->name('bagistoapi.graphql-playground');

        Route::get('/api/graphql', GraphQLPlaygroundController::class)
            ->name('bagistoapi.api-graphql-playground');

        Route::get('/admin/graphiql', AdminGraphQLPlaygroundController::class)
            ->name('bagistoapi.admin-graphql-playground');

        Route::get('/api/shop/customer-invoices/{id}/pdf', InvoicePdfController::class)
            ->where('id', '[0-9]+')
            ->middleware(['Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey'])
            ->name('bagistoapi.customer-invoice-pdf');

        Route::get('/api/downloadable/download-sample/{type}/{id}', DownloadSampleController::class)
            ->where('type', 'link|sample')
            ->where('id', '[0-9]+')
            ->name('bagistoapi.download-sample');

        Route::get('/api/shop/customer-downloadable-products/{id}/download', DownloadablePurchasedController::class)
            ->where('id', '[0-9]+')
            ->middleware(['Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey'])
            ->name('bagistoapi.customer-downloadable-product-download');
    }

    /**
     * Register API resources.
     */
    protected function registerApiResources(): void
    {
        if ($this->app->bound('api_platform.metadata_factory')) {
        }
    }

    /**
     * Run installation if needed.
     */
    protected function runInstallationIfNeeded(): void
    {
        if (file_exists(config_path('api-platform.php'))) {
            return;
        }

        if (! $this->app->runningInConsole() || ! $this->isComposerOperation()) {
            return;
        }

        try {
            $this->app['artisan']->call('bagisto-api-platform:install', ['--quiet' => true]);
        } catch (\Exception) {
            // Installation can be run manually if needed
        }
    }

    /**
     * Determine if running via Composer.
     */
    protected function isComposerOperation(): bool
    {
        $composerMemory = getenv('COMPOSER_MEMORY_LIMIT');
        $composerAuth = getenv('COMPOSER_AUTH');

        return ! empty($composerMemory) || ! empty($composerAuth) || defined('COMPOSER_BINARY_PATH');
    }

    /**
     * Register middleware aliases.
     */
    protected function registerMiddlewareAliases(): void
    {
        $this->app['router']->aliasMiddleware('storefront.key', VerifyStorefrontKey::class);
        $this->app['router']->aliasMiddleware('api.locale-channel', SetLocaleChannel::class);
        $this->app['router']->aliasMiddleware('api.rate-limit', RateLimitApi::class);
        $this->app['router']->aliasMiddleware('api.security-headers', SecurityHeaders::class);
        $this->app['router']->aliasMiddleware('api.log-requests', LogApiRequests::class);
    }

    /**
     * Register service providers.
     */
    protected function registerServiceProviders(): void
    {
        $this->app->register(ApiPlatformExceptionHandlerServiceProvider::class);
        $this->app->register(DatabaseQueryLoggingProvider::class);
        $this->app->register(ExceptionHandlerServiceProvider::class);
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            InstallApiPlatformCommand::class,
            ClearApiPlatformCacheCommand::class,
            GenerateStorefrontKey::class,
            ApiKeyManagementCommand::class,
            ApiKeyMaintenanceCommand::class,
        ]);
    }

    /**
     * Override API Platform's ItemProvider and CollectionProvider to wrap the
     * LinksHandler with SnakeCaseLinksHandler, fixing the camelCase/snake_case
     * mismatch between GraphQL field names and Eloquent relationship names.
     */
    protected function registerSnakeCaseLinksHandlerFix(): void
    {
        $this->app->extend(
            ItemProvider::class,
            function ($original, $app) {
                $linksHandler = new SnakeCaseLinksHandler(
                    new LinksHandler(
                        $app,
                        $app->make(ResourceMetadataCollectionFactoryInterface::class)
                    )
                );

                $tagged = iterator_to_array($app->tagged(LinksHandlerInterface::class));

                return new ItemProvider(
                    $linksHandler,
                    new ServiceLocator($tagged),
                    $app->tagged(QueryExtensionInterface::class)
                );
            }
        );

        $this->app->extend(
            CollectionProvider::class,
            function ($original, $app) {
                $linksHandler = new SnakeCaseLinksHandler(
                    new LinksHandler(
                        $app,
                        $app->make(ResourceMetadataCollectionFactoryInterface::class)
                    )
                );

                $tagged = iterator_to_array($app->tagged(LinksHandlerInterface::class));

                return new CollectionProvider(
                    $app->make(Pagination::class),
                    $linksHandler,
                    $app->tagged(QueryExtensionInterface::class),
                    new ServiceLocator($tagged)
                );
            }
        );
    }

    /**
     * Check if the package is running as a vendor package.
     */
    protected function isRunningAsVendorPackage(): bool
    {
        return str_contains(__DIR__, 'vendor');
    }
}
