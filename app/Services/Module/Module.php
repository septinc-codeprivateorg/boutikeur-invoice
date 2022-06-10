<?php

namespace Crater\Services\Module;

class Module
{
    /**
     * All of the registered Modules scripts.
     *
     * @var array
     */
    public static array $scripts = [];

    /**
     * All of the registered company settings.
     *
     * @var array
     */
    public static array $settings = [];

    /**
     * All of the registered Modules CSS.
     *
     * @var array
     */
    public static array $styles = [];

    /**
     * Register the given script file with Module.
     *
     * @param string $name
     * @param string $path
     *
     * @return static
     */
    public static function script(string $name, string $path): Module
    {
        static::$scripts[$name] = $path;

        return new static();
    }

    /**
     * Register the given CSS file with Module.
     *
     * @param  string  $name
     * @param  string  $path
     * @return static
     */
    public static function style(string $name, string $path): Module
    {
        static::$styles[$name] = $path;

        return new static();
    }

    /**
     * Get all of the additional scripts that should be registered.
     *
     * @return array
     */
    public static function allScripts(): array
    {
        return static::$scripts;
    }

    /**
     * Get all of the additional stylesheets that should be registered.
     *
     * @return array
     */
    public static function allStyles(): array
    {
        return static::$styles;
    }
}
