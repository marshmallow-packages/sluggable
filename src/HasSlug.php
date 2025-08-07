<?php

namespace Marshmallow\Sluggable;

use ReflectionMethod;
use ReflectionParameter;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redirect;
use Marshmallow\Redirectable\Facades\Redirector;
use Marshmallow\Sluggable\Events\SlugWasCreated;
use Marshmallow\Sluggable\Events\SlugWasDeleted;
use Marshmallow\Sluggable\Events\SlugHasBeenChanged;
use Marshmallow\Sluggable\Contracts\TranslatableSlug;
use Illuminate\Database\UniqueConstraintViolationException;

trait HasSlug
{
    public SlugOptions $slugOptions;

    /**
     * Return an array of artisan commands that
     * need to be run to make sure slug information
     * is available. By default we run route:clear and route:cache
     * if the routes are cached.
     */
    public function getArtisanCommands(): array
    {
        return [
            'route:cache',
        ];
    }

    /**
     * We run the artisan command of this method results in
     * a true. This is added so we only run route:cache if the
     * routes are in fact cached.
     */
    public function runArtisanCommandsWhen(): bool
    {
        return app()->routesAreCached();
    }

    public function getAndParseSlugOptions(): SlugOptions
    {
        $options = $this->getSlugOptions();
        if ($this instanceof TranslatableSlug) {
            if (!$options->usingLanguageIsset()) {
                $options->usingLanguage(request()->getTranslatableLocale());
            }
        }

        return $options;
    }

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        if (isset($this->slugOptions)) {
            return $this->slugOptions;
        }

        return $this->getDefaultSlugOptions();
    }

    public function getDefaultSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    protected static function bootHasSlug()
    {
        static::creating(function (Model $model) {
            $model->generateSlugOnCreate();
        });

        static::updating(function (Model $model) {
            $model->generateSlugOnUpdate();
        });

        static::created(function (Model $model) {
            event(new SlugWasCreated($model));
        });

        static::updated(function (Model $model) {
            $slugField = $model->slugOptions->slugField;
            if ($model->isDirty($slugField)) {

                /**
                 * Add a record to the redirector
                 */
                if (method_exists($model, 'redirectable')) {

                    $redirect_this = $model->original[$slugField];
                    $to_this = $model->{$slugField};

                    if (method_exists($model, 'route')) {
                        $method = new \ReflectionMethod(get_class($model), 'route');
                        $parameters = $method->getParameters();
                        $slug_parameter_exists = collect($parameters)
                            ->filter(function (ReflectionParameter $parameter) {
                                return $parameter->name === 'slug';
                            })->count() === 1;

                        if ($slug_parameter_exists) {
                            $redirect_this = $model->route($redirect_this);
                            $to_this = $model->route();

                            $redirect_this = Str::of($redirect_this)->remove(config('app.url') . '/');
                            $to_this = Str::of($to_this)->remove(config('app.url') . '/');
                        }
                    }

                    try {
                        $model = Redirector::add(
                            $model,
                            redirect_this: $redirect_this,
                            to_this: $to_this,
                        );
                    } catch (\Illuminate\Database\UniqueConstraintViolationException) {
                    }
                }

                /**
                 * Trigger event so we can recache the routes.
                 */
                event(new SlugHasBeenChanged($model));
            }
        });
        static::deleted(function (Model $model) {
            /**
             * Delete all items from the redirector.
             */
            if (method_exists($model, 'redirectable')) {
                $model->redirectable()->delete();
            }

            /**
             * Trigger event so we can recache the routes.
             */
            event(new SlugWasDeleted($model));
        });
    }

    protected function generateSlugOnCreate()
    {
        $this->slugOptions = $this->getAndParseSlugOptions();
        if (!$this->slugOptions->generateSlugsOnCreate) {
            return;
        }

        $this->addSlug();
    }

    /**
     * Check if the slug field is dirty and not empty
     */
    protected function slugFieldIsDirty()
    {
        $slugField = $this->slugOptions->slugField;
        return $this->isDirty($slugField) && $this->{$slugField};
    }

    protected function slugFieldIsClean()
    {
        return !$this->slugFieldIsDirty();
    }

    protected function generateSlugOnUpdate()
    {
        $this->slugOptions = $this->getAndParseSlugOptions();
        if (!$this->slugOptions->generateSlugsOnUpdate && $this->{$this->slugOptions->slugField} !== null) {
            return;
        }

        if ($this->slugFieldIsClean()) {
            return;
        }

        $this->addSlug();
    }

    public function generateSlug()
    {
        $this->slugOptions = $this->getAndParseSlugOptions();

        $this->addSlug();
    }

    protected function addSlug()
    {
        $this->ensureValidSlugOptions();

        $slug = $this->generateNonUniqueSlug();

        if ($this->slugOptions->generateUniqueSlugs) {
            $slug = $this->makeSlugUnique($slug);
        }

        $slugField = $this->slugOptions->slugField;

        $this->$slugField = $slug;
    }

    protected function generateNonUniqueSlug(): string
    {
        $slugField = $this->slugOptions->slugField;

        if ($this->hasCustomSlugBeenUsed() && !empty($this->$slugField) && $this->slugFieldIsClean()) {
            return $this->$slugField;
        }

        return Str::slug($this->getSlugSourceString(), $this->slugOptions->slugSeparator, $this->slugOptions->slugLanguage);
    }

    protected function hasCustomSlugBeenUsed(): bool
    {
        $slugField = $this->slugOptions->slugField;

        return $this->getOriginal($slugField) != $this->$slugField;
    }

    protected function getSlugSourceString(): string
    {
        if ($this->slugFieldIsDirty()) {
            $slugField = $this->slugOptions->slugField;
            return $this->{$slugField};
        }

        if (is_callable($this->slugOptions->generateSlugFrom)) {
            $slugSourceString = $this->getSlugSourceStringFromCallable();

            return $this->generateSubstring($slugSourceString);
        }

        $slugSourceString = collect($this->slugOptions->generateSlugFrom)
            ->map(fn (string $fieldName): string => data_get($this, $fieldName, ''))
            ->implode($this->slugOptions->slugSeparator);

        return $this->generateSubstring($slugSourceString);
    }

    protected function getSlugSourceStringFromCallable(): string
    {
        return call_user_func($this->slugOptions->generateSlugFrom, $this);
    }

    protected function makeSlugUnique(string $slug): string
    {
        $originalSlug = $slug;
        $i = 1;

        while ($this->otherRecordExistsWithSlug($slug) || $slug === '') {
            $slug = $originalSlug . $this->slugOptions->slugSeparator . $i++;
        }

        return $slug;
    }

    protected function otherRecordExistsWithSlug(string $slug): bool
    {
        $key = $this->getKey();

        if ($this->getIncrementing()) {
            $key ??= '0';
        }

        $query = static::where($this->slugOptions->slugField, $slug)
            ->where($this->getKeyName(), '!=', $key)
            ->withoutGlobalScopes();

        if ($this->usesSoftDeletes()) {
            $query->withTrashed();
        }

        return $query->exists();
    }

    protected function usesSoftDeletes(): bool
    {
        return (bool) in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this));
    }

    protected function ensureValidSlugOptions()
    {
        if (is_array($this->slugOptions->generateSlugFrom) && !count($this->slugOptions->generateSlugFrom)) {
            throw InvalidOption::missingFromField();
        }

        if (!strlen($this->slugOptions->slugField)) {
            throw InvalidOption::missingSlugField();
        }

        if ($this->slugOptions->maximumLength <= 0) {
            throw InvalidOption::invalidMaximumLength();
        }
    }

    /**
     * Helper function to handle multi-bytes strings if the module mb_substr is present,
     * default to substr otherwise.
     */
    protected function generateSubstring($slugSourceString)
    {
        return mb_substr($slugSourceString, 0, $this->slugOptions->maximumLength);
    }
}
