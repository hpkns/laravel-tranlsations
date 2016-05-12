<?php

namespace Hpkns\Translations;

use Illuminate\Support\ServiceProvider;

class TranslationsServiceProvider extends ServiceProvider
{
    /**
     * The Artisan commands provided by this service provider.
     *
     * @var array
     */
    protected $commands = [
        ImportTranslationsCommand::class,
        CleanTranslationsCommand::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands($this->commands);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
