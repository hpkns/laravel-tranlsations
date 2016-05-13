<?php

namespace Hpkns\Translations\Commands;

use Illuminate\Console\Command;
use Hpkns\Translations\TranslationRepository;

class ExportTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:export
                                {--output=php://stdout : Where to save the translations table (default is stdout)}
                                {--only= : Limit languages to export}
                                {--except= : List of languages to exclude}
                                {--namespace_only= : Limit the namespaces}
                                {--namespace_except= : Limit the namespaces}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export translations in CSV format';

    /**
     * A translation repository to do the heavy lifting.
     *
     * @param Hpkns\Translations\TranslationRepository
     */
    protected $translations;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TranslationRepository $translations)
    {
        parent::__construct();

        $this->translations = $translations;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $locales = $this->getLocales();
        $namespaces = $this->getNamespaces();

        $fh = fopen($this->option('output'), 'w');

        foreach ($this->translations->getTable($locales, $namespaces) as $line) {
            fputcsv($fh, $line, ';');
        }
    }

    /**
     * Return all the locales, filted.
     *
     * @return array
     */
    public function getLocales()
    {
        return $this->translations->getLocales(
            $this->split($this->option('only')),
            $this->split($this->option('except'))
        );
    }

    /**
     * Return the namespaces associated with a set of locales.
     *
     * @param  array $locales
     * @return array
     */
    public function getNamespaces($locales = [])
    {
        return $this->translations->getNamespaces(
            $locales,
            $this->split($this->option('namespace_only')),
            $this->split($this->option('namespace_except'))
        );
    }

    protected function split($str)
    {
        if (empty(trim($str))) {
            return [];
        }

        return array_map('trim', preg_split('#[,;]#', $str));
    }


}
