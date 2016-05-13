<?php

use Mockery as m;
use Hpkns\Translations\TranslationRepository;

class TranslationsRepositoryTests extends \PHPUNIT_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testIsInstanciable()
    {
        $this->assertInstanceOf(TranslationRepository::class, new TranslationRepository);
    }


    public function testGetLocales()
    {
        $r = m::mock(TranslationRepository::class . '[]');

        $r->setTranslationPath('stubs');

        $this->assertEquals(array_values($r->getLocales(['fr', 'ru', 'nl'])), ['fr', 'nl']);
        $this->assertEquals(array_values($r->getLocales([], ['en'])), ['fr', 'nl']);
    }

    public function testGetTable()
    {
        $r = m::mock(TranslationRepository::class . '[getTranslations]');
        $locales = ['fr', 'en'];
        $namespaces = ['validation'];
        $translations = [
            'fr' => ['key1' => 'foo','key2' => 'bar'],
            'en' => ['key3' => 'baz']
        ];
        $result = [
            ['key', 'fr', 'en'],
            ['key1', 'foo', null],
            ['key2', 'bar', null],
            ['key3', null, 'baz']
        ];

        $r->shouldReceive('getTranslations')->once()->with($locales, $namespaces)->andReturn($translations);

        $this->assertEquals($result, $r->getTable($locales, $namespaces));
    }

    public function testGetKeys()
    {
        $r = new TranslationRepository;

        $base = [
            'fr' => ['key1' => 'foo','key2' => 'bar'],
            'en' => ['key1' => 'baz','key3' => 'baz']
        ];

        $this->assertEquals($r->getKeys($base), ['key1', 'key2', 'key3']);
    }

    public function testPathAccessors()
    {
        $r = new TranslationRepository;
        $r->setTranslationPath('foo');

        $this->assertEquals($r->getTranslationsPath(), __DIR__ . '/foo');
    }
}
