<?php
/**
 * Builder class file.
 *
 * @package Extremis
 */

namespace XWP\Extremis;

/**
 * Extremis theme builder.
 */
class Builder {
    /**
     * Whether the builder has been run.
     *
     * @var bool
     */
    private static bool $built = false;

    /**
     * Handlers to register.
     *
     * @var array<int,class-string>
     */
    private static array $handlers;

    /**
     * Modules to import.
     *
     * @var array<int,class-string>
     */
    private static array $modules;

    /**
     * Configuration array.
     *
     * @var array<string,mixed>
     */
    private static array $config;

    /**
     * Build the theme.
     *
     * @param array<int,class-string> $modules    Array of submodules to import.
     * @param array<int,class-string> $handlers   Array of handlers to register.
     * @param string                  $textdomain Textdomain.
     * @param string                  $version    Version.
     */
    public static function build( array $modules, array $handlers, string $textdomain, string $version ): void {
        if ( self::$built ) {
            return;
        }

        self::$built    = true;
        self::$handlers = $handlers;
        self::$modules  = $modules;
        self::$config   = array(
            'cfg.textdomain' => $textdomain,
            'cfg.version'    => $version,
        );

        \add_filter( 'xwp_extend_import_extremis', array( self::class, 'set_imports' ), 10, 2 );
        \add_filter( 'xwp_extend_handlers_extremis', array( self::class, 'set_handlers' ), 10, 2 );
        \add_filter( 'xwp_extend_config_extremis', array( self::class, 'set_config' ), 10, 2 );

        \xwp_load_app(
            array(
                'compile_dir' => \get_stylesheet_directory() . 'compile',
                'id'          => 'extremis',
                'module'      => Theme::class,
            ),
            hook: 'after_setup_theme',
            priority: -1001,
        );
    }

    /**
     * Set the Extremis handlers.
     *
     * @param  array<int,class-string> $handlers Handlers.
     * @return array<int,class-string>
     */
    public static function set_handlers( array $handlers ): array {
        return \array_merge( $handlers, self::$handlers );
    }

    /**
     * Set the Extremis imports.
     *
     * @param  array<int,class-string> $imports   Imports.
     * @param  class-string            $classname Classname.
     * @return array<int,class-string>
     */
    public static function set_imports( array $imports, string $classname ): array {
        if ( Theme::class === $classname ) {
            $imports = \array_merge( $imports, self::$modules );
        }

        return $imports;
    }

    /**
     * Set the Extremis configuration.
     *
     * @param  array<string,mixed> $config Configuration.
     * @return array<string,mixed>
     */
    public static function set_config( array $config ): array {
        return \array_merge( $config, self::$config );
    }
}
