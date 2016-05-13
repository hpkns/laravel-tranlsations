<?php

namespace Hpkns\Translations\Commands;

use Illuminate\Console\Command;

class ImportTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:import
                            {--s|source= : The location of the CSV file (can be an URL)}
                            {--d|delimiter=,: The CSV delimiter}
                            {--e|allow_empty : Allow empty values}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batch import translations from a CSV file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $languages = $this->getTranslations($this->option('source'));

        foreach ($languages as $locale => $files) {
            foreach ($files as $file => $translations) {
                $new = $this->updateFile($file, $locale, $translations);
                (new Support\ArrayDumper)->dumpToFile($new, base_path( "resources/lang/{$locale}/{$file}.php"));
            }
        }
    }

    /**
     * Add new translation to a translation file.
     *
     * @param  string $fname
     * @param  string $locale
     * @param  array  $translations
     * @return void
     */
    public function updateFile($fname, $locale, array $translations = [])
    {
        $file = app('translator')->get($fname, [], $locale, false);
        if (is_string($file)) {
            $file = [];
        }

        foreach ($translations as $key => $value) {
            if (! $this->option('allow_empty') && empty($value)) {
                continue;
            }

            array_set($file, $key, $value);
        }

        return $file;
    }

    /**
     * Get translations sorted by language and translation file.
     *
     * @param  string $source
     * @return array
     */
    public function getTranslations($source)
    {
        if (! file_exists($source)) {
            throw new \LogicException("File [{$source}] does not appear to exists.");
        }

        $delimiter = $this->option('delimiter');
        $fh = fopen($source, 'r');
        $headers = $this->parseCsvHeaders(fgetcsv($fh, 0, $delimiter));
        $locales = [];
        $languages = [];

        foreach ($headers as $header) {
            if ($header != 'key') {
                $locales[] = $header;
                $languages[$header] = [];
            }
        }

        while ($line = fgetcsv($fh, 0, $delimiter)) {
            $line = array_combine($headers, $line);

            foreach ($locales as $locale) {
                $languages[$locale][$line['key']] = $line[$locale];
            }
        }

        return array_map([$this, 'sortByFile'], $languages);
    }

    /**
     * Parse a CSV file headers
     *
     * @param  array $headers
     * @return array
     */
    protected function parseCsvHeaders(array $headers)
    {
        $headers[0] = 'key';
        $headers = array_map('strtolower', $headers);
        return $headers;
    }

    /**
     * Sort translations keys by locale file name.
     *
     * @param  array $translations
     * @return array
     */
    protected function sortByFile($translations)
    {
        $files = [];

        foreach ($translations as $key => $translation) {
            $point_position = strpos($key, '.');
            $file = substr($key, 0, $point_position);
            $files[$file][substr($key, $point_position + 1)] = $translation;
        }

        return $files;
    }

}
