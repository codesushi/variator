<?php

declare (strict_types=1);

namespace Codesushi\Variator\Exception;

class Helper
{
    public static function fromPath(string $path) : CircularDependencyException
    {
        return new CircularDependencyException(sprintf('Found circular dependency at path "%s" while building variator config.', $path));
    }
}
