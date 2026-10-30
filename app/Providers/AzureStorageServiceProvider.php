<?php

namespace App\Providers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

class AzureStorageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Storage::extend('azure', function ($app, $config) {
            // Use BlobRestProxy to create the Blob Storage client
            $client = BlobRestProxy::createBlobService($config['connection_string']);

            // Create the Azure Blob Storage adapter (Flysystem v3)
            $adapter = new AzureBlobStorageAdapter(
                $client,
                $config['container']
            );

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }

    public function register()
    {
        //
    }
}
