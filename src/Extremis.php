<?php
/**
 * Extremis class file.
 *
 * @package Extremis
 */

namespace Extremis;

use Oblak\WP\Asset_Loader;
use Oblak\WP\Loader_Trait;

/**
 * Main child theme class
 */
class Extremis {
    use Loader_Trait;

    /**
     * Theme modules
     *
     * @var object[]
     */
    private array $modules;

    /**
     * Asset config array
     *
     * @var array
     */
    private array $assets;

    /**
     * Class instance
     *
     * @var Extremis
     */
    private static ?Extremis $instance = null;

    /**
     * Class constructor
     */
    private function __construct() {
        $this->modules = file_exists( locate_template( '/config/modules.php' ) )
            ? require locate_template( 'config/modules.php' )
            : array();

        $this->assets = file_exists( locate_template( '/config/assets.php' ) )
            ? require locate_template( 'config/assets.php' )
            : array();

        $this->init_hooks();
    }

    /**
     * Initializes the hooks.
     */
    private function init_hooks() {
        add_action( 'after_setup_theme', array( $this, 'load_textdomain' ), 0 );
        add_action( 'after_setup_theme', array( $this, 'init_asset_loader' ), 1 );
        add_action( 'after_setup_theme', array( $this, 'init_modules' ), 2 );

        add_action( 'widgets_init', array( $this, 'init_widgets' ) );

        add_filter( 'body_class', array( $this, 'modify_body_class' ), 99, 1 );
    }

    /**
     * Get class instance
     *
     * @return Extremis
     */
    public static function get_instance(): Extremis {
        return self::$instance ?? self::$instance = new static(); //phpcs:ignore
    }

    /**
     * Loads the textdomain
     */
    public function load_textdomain() {
        load_child_theme_textdomain(
            defined( 'EXTREMIS_TEXTDOMAIN' ) ? EXTREMIS_TEXTDOMAIN : 'extremis',
            get_stylesheet_directory() . '/languages'
        );
    }

    /**
     * Initializes the asset loader.
     */
    public function init_asset_loader() {
        ! empty( $this->assets ) && Asset_Loader::get_instance()->register_namespace( $this->namespace, $this->assets );
    }

    /**
     * Initializes the modules.
     */
    public function init_modules() {
        foreach ( $this->modules as $module_name => $module_classname ) {
            if ( ! is_admin() && str_contains( $module_name, 'admin' ) || str_contains( $module_name, 'widget' ) ) {
                continue;
            }

            $this->modules[ $module_name ] = new $module_classname();
        }
    }

    /**
     * Initialies widgets
     */
    public function init_widgets() {
        foreach ( $this->modules as $module_name => $module_classname ) {
            if ( ! str_contains( $module_name, 'widget' ) ) {
                continue;
            }

            register_widget( $module_classname );
        }
    }

    /**
     * Adds the current page slug to the body class.
     *
     * @param  string[] $classes Current body classes.
     * @return string[]          Modified body classes.
     */
    public function modify_body_class( array $classes ): array {
        if ( is_single() || is_page() && ! is_front_page() ) {
            if ( ! in_array( basename( get_permalink() ), $classes, true ) ) {
                $classes[] = basename( get_permalink() );
            }
        }

        return array_filter( $classes );
    }

    /**
     * Get the module class
     *
     * @param  string $module Module name.
     * @return object|null    Module class, or null if not found.
     */
    public function get_module( string $module ): ?object {
        return $this->modules[ $module ] ?? null;
    }
}
