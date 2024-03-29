<?php

namespace Marshmallow\Sluggable;

class SlugOptions
{
    /** @var array|callable */
    public $generateSlugFrom;

    public string $slugField;

    public bool $generateUniqueSlugs = true;

    public int $maximumLength = 250;

    public bool $generateSlugsOnCreate = true;

    public bool $generateSlugsOnUpdate = false;

    public string $slugSeparator = '-';

    public bool $slugLanguageSet = false;

    public string $slugLanguage = 'en';

    public array $translatableLocales = [];

    public static function create(): self
    {
        return new static();
    }

    public static function createWithLocales(array $locales): self
    {
        $slugOptions = static::create();

        $slugOptions->translatableLocales = $locales;

        return $slugOptions;
    }

    /**
     * @param string|array|callable $fieldName
     *
     * @return \Marshmallow\Sluggable\SlugOptions
     */
    public function generateSlugsFrom($fieldName): self
    {
        if (is_string($fieldName)) {
            $fieldName = [$fieldName];
        }

        $this->generateSlugFrom = $fieldName;

        return $this;
    }

    public function saveSlugsTo(string $fieldName): self
    {
        $this->slugField = $fieldName;

        return $this;
    }

    public function allowDuplicateSlugs(): self
    {
        $this->generateUniqueSlugs = false;

        return $this;
    }

    public function slugsShouldBeNoLongerThan(int $maximumLength): self
    {
        $this->maximumLength = $maximumLength;

        return $this;
    }

    public function doNotGenerateSlugsOnCreate(): self
    {
        $this->generateSlugsOnCreate = false;

        return $this;
    }

    /**
     * This method is deprecated. It doesnt generate a new
     * slug on updates is now the default behauvior.
     */
    public function doNotGenerateSlugsOnUpdate(): self
    {
        $this->generateSlugsOnUpdate = false;

        return $this;
    }

    public function generateSlugsOnUpdate(): self
    {
        $this->generateSlugsOnUpdate = true;

        return $this;
    }

    public function usingSeparator(string $separator): self
    {
        $this->slugSeparator = $separator;

        return $this;
    }

    public function usingLanguage(string $language): self
    {
        $this->slugLanguage = $language;
        $this->slugLanguageSet = true;

        return $this;
    }

    public function usingLanguageIsset(): bool
    {
        return $this->slugLanguageSet;
    }
}
