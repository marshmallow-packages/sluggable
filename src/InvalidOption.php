<?php

namespace Marshmallow\Sluggable;

use Exception;

class InvalidOption extends Exception
{
    public static function missingFromField()
    {
        return new static('Could not determine which fields should be sluggified', 1);
    }

    public static function missingSlugField()
    {
        return new static('Could not determine in which field the slug should be saved', 2);
    }

    public static function invalidMaximumLength()
    {
        return new static('Maximum length should be greater than zero', 3);
    }
}
