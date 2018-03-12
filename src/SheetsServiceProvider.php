<?php

namespace Spatie\Sheets;

use Illuminate\Support\ServiceProvider;
use League\CommonMark\CommonMarkConverter;
use Spatie\Sheets\ContentParsers\MarkdownParser;
use Spatie\Sheets\ContentParsers\MarkdownWithFrontMatterParser;
use Spatie\Sheets\PathParsers\SlugParser;
use Illuminate\Filesystem\FilesystemManager;

class SheetsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/sheets.php' => config_path('sheets.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sheets.php', 'sheets');

        $this->app->when(MarkdownWithFrontMatterParser::class)
            ->needs(CommonMarkConverter::class)
            ->give(function () {
                return new CommonMarkConverter();
            });

        $this->app->when(MarkdownParser::class)
            ->needs(CommonMarkConverter::class)
            ->give(function () {
                return new CommonMarkConverter();
            });

        $this->app->singleton(Sheets::class, function () {
            $sheets = new Sheets();

            foreach (config('sheets.collections', []) as $name => $options) {
                if (is_int($name)) {
                    $name = $options;
                    $options = [];
                }

                $sheets->registerCollection(
                    $name,
                    $this->app->make($options['path_parser'] ?? SlugParser::class),
                    $this->app->make($options['content_parser'] ?? MarkdownWithFrontMatterParser::class),
                    $options['sheet_class'] ?? Sheet::class,
                    $this->app->make(FilesystemManager::class)->disk($options['disk'] ?? $name)
                );
            }

            if (config('sheets.default')) {
                $sheets->setDefaultCollection(config('sheets.default'));
            }

            return $sheets;
        });

        $this->app->alias(Sheets::class, 'sheets');
    }
}
