<?php

namespace Hpkns\Translations;

use Illuminate\Console\Command;
use Symfony\Component\VarDumper\Cloner\VarCloner;

class CleanTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean the translations.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->filesystem = app('files');
        $this->dumper = app(Support\ArrayDumper::class);
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $base = base_path('resources/lang');

        foreach ($this->filesystem->directories($base) as $locale) {
            foreach ($this->filesystem->files($base . DIRECTORY_SEPARATOR . basename($locale)) as $path) {
                $this->dumper->dumpToFile(
                    $this->cleanArray(require($path), [basename($locale) . ':' . str_replace('.php', '', basename($path))]),
                    $path
                );
            }
        }
    }

    /**
     * Rucursiveley filter an array.
     *
     * @param  array $array
     * @return array
     */
    public function cleanArray(array $array, $tranlsation_key = [])
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->cleanArray($array[$key], array_merge($tranlsation_key, [$key]));
            }

            if (empty($array[$key])) {
                $deleted_key = implode('.', array_merge($tranlsation_key, [$key]));
                unset($array[$key]);
                $this->info("Deleting key [{$deleted_key}]");
            }
        }

        return $array;
    }
}
