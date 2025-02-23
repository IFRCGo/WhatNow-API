<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;

class AzureStorageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        \Storage::extend('azure', function($app, $config) {
            // Usar BlobRestProxy para crear el cliente de Blob Storage
            $client = BlobRestProxy::createBlobService($config['connection_string']);

            // Crear el adaptador para Azure Blob Storage
            $adapter = new AzureBlobStorageAdapter(
                $client,
                $config['container']
            );

            return new Filesystem($adapter);
        });
    }

    public function register()
    {
        //
    }
}
