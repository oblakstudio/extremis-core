<?php

namespace Extremis;

use Composer\Script\Event;

/**
 * AutoConstructor class creates modules.php for Extremis Module auto-construction
 */
final class AutoConstructor
{

    private static $excludes;

    private static $io;

    /**
     * Generates modules classmap file
     *
     * @param  Event $event
     * @return int
     */
    public static function run(Event $event): int
    {

        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        self::$io  = $event->getIO();

        self::$excludes = require_once dirname($vendorDir) . '/config/excludes.php';

        $classnames = array_keys(array_filter(
            require_once $vendorDir . '/composer/autoload_classmap.php',
            ['Extremis\\AutoConstructor', 'filterClassnameArray'],
            ARRAY_FILTER_USE_BOTH
        ));

        return (self::generateIncludeFile($classnames, $vendorDir))
            ? 1
            : 0;
    }

    /**
     * Filters Extremis classes in framework folder from other classes
     *
     * @param  string $path      Vendor directory path
     * @param  string $classname Class name to check
     * @return bool              True if Extremis class in framework folder, false if not
     */
    public static function filterClassnameArray(string $path, string $classname)
    {

        $is_extremis = (
            (strpos($classname, 'Extremis') !== false) &&
            (strpos($path, 'framework') !== false) &&
            (strpos($classname, 'Abstract') === false) &&
            (strpos($classname, 'Interface') === false) &&
            (strpos($classname, 'Trait') === false)
        );

        if (!$is_extremis) :
            return false;
        endif;

        $is_excluded = in_array($classname, self::$excludes);

        return !$is_excluded;
    }

    /**
     * Generates modules.php file in config directory
     *
     * @param  array  $classnames Classnames to auto construct
     * @param  string $vendorDir  Full path to vendor directory
     * @return int|false          Number of bytes that were written to the file, or false on failure.
     */
    public static function generateIncludeFile(array $classnames, string $vendorDir)
    {

        $output = "<?php\n/**\n * Extremis Modules\n *\n * @package Extremis\n */\n\nreturn array(\n" ;

        $max_length = array_reduce($classnames, function ($carry, $classname) {
            $length = strlen(self::getClassPath($classname));

            return ($length > $carry) ? $length : $carry;
        }, 0);

        foreach ($classnames as $classname) {
            $class_path = self::getClassPath($classname);

            $output .= sprintf(
                "    '%s'%s => '%s',\n",
                $class_path,
                str_repeat(' ', $max_length - strlen($class_path)),
                str_replace('\\', '\\\\', $classname)
            );
        }

        $output .= ");\n";

        return file_put_contents("{$vendorDir}/../config/modules.php", $output);
    }

    private static function getClassPath(string $classname): string
    {
        $exploded   = explode('\\', $classname);
        $class      = strtolower(array_pop($exploded));
        $namespace  = strtolower(array_pop($exploded));

        return "{$namespace}-{$class}";
    }
}
