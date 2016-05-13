<?php

namespace Hpkns\Translations;

class TranslationRepository
{
    /**
     * The location of the translations relative to Laravel's base path.
     *
     * @var string
     */
    protected $path = 'resources/lang';

    /**
     * Return all the locales that have translations files.
     *
     * @return array
     */
    public function getLocales($include = [], $exclude = [])
    {
        $locales = array_map('basename', array_filter(glob($this->getTranslationsPath() . DIRECTORY_SEPARATOR . '*'), 'is_dir'));

        if (! empty($include)) {
            $locales = array_intersect($locales, $include);
        }

        if (! empty($exclude)) {
            $locales = array_diff($locales, $exclude);
        }

        return $locales;
    }

    /**
     * Return every namepsaces (possibly limites to the locales in $locale).
     *
     * @param  array $locales
     * @return array
     */
    public function getNamespaces($locales = [], $include = [], $exclude = [])
    {
        $locales = $locales ?: $this->getLocales();
        $namespaces = [];

        foreach ($locales as $locale) {
            $namespaces = array_merge(
                $namespaces,
                array_map(function ($path) {
                    return str_replace('.php', '', basename($path));

                }, glob($this->getTranslationsPath() . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . '*.php'))
            );
        }

        if (! empty($include)) {
            $namespaces = array_intersect($namespaces, $include);
        }

        if (! empty($exclude)) {
            $namespaces = array_diff($namespaces, $exclude);
        }

        return array_unique($namespaces);
    }

    /**
     * Return a table containing translations.
     *
     * @param  array $locales
     * @param  array $namespaces
     * @return array
     */
    public function getTable(array $locales, array $namespaces)
    {
        $translations = $this->getTranslations($locales, $namespaces);
        $keys = $this->getKeys($translations);

        $table = [array_merge(['key'], $locales)];

        foreach ($keys as $key) {
            $line = [$key];

            foreach ($locales as $locale) {
                $line[] = array_get($translations[$locale], $key, null);
            }

            $table[] = $line;
        }

        return $table;
    }

    /**
     * Return all the locales in the $locales locales and $namespace namespace.
     *
     * @param  array $locales
     * @param  array $namespace
     * @return array
     */
    public function getTranslations(array $locales, array $namespaces)
    {
        $translations = [];

        foreach ($locales as $locale) {
            $translations[$locale] = [];

            foreach ($namespaces as $namespace) {
                $translations[$locale] = array_dot(array_merge($translations[$locale], [$namespace => require($this->getTranslationsPath() . "/{$locale}/{$namespace}.php")]));
            }
        }

        return $translations;
    }

    /**
     * Return all the keys in a set of translations.
     *
     * @param  array $translations
     * @return array
     */
    public function getKeys(array $translations)
    {
        $keys = [];

        foreach ($translations as $_ => $t) {
            $keys = array_merge($keys, array_keys($t));
        }

        return array_values(array_unique($keys));
    }

    /**
     * Set the translation path.
     *
     * @param  string $path
     * @return void
     */
    public function setTranslationPath($path)
    {
        $this->path = $path;
    }

    /**
     * Return the path to the translations directory.
     *
     * @return string
     */
    public function getTranslationsPath()
    {
        return base_path($this->path);
    }
}
