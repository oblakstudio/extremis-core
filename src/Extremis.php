<?php
/**
 * Extremis class file.
 *
 * @package Extremis
 */

namespace Extremis;

use Oblak\WP\Asset_Loader;
use Oblak\WP\Loader_Trait;
use Oblak\WP\Traits\Hook_Processor_Trait;
use Oblak\WP\Traits\Singleton_Trait;

/**
 * Main child theme class
 */
class Extremis {
    use Loader_Trait;
    use Singleton_Trait;
    use Hook_Processor_Trait;

    /**
     * Asset config array
     *
     * @var array
     */
    private array $assets;

    /**
     * Class constructor
     */
    private function __construct() {
        $this->namespace = 'extremis';
        $this->assets    = file_exists( locate_template( '/config/assets.php' ) )
            ? require locate_template( '/config/assets.php' )
            : array();

        $this->init( 'after_setup_theme', 0 );
    }

    /**
     * {@inheritDoc}
     */
    protected function get_dependencies(): array {
        return require locate_template( '/config/dependencies.php' );
    }

    /**
     * Loads the textdomain
     *
     * @hook     after_setup_theme
     * @type     action
     * @priority 1
     */
    public function load_textdomain() {
        load_child_theme_textdomain(
            defined( 'EXTREMIS_TEXTDOMAIN' ) ? EXTREMIS_TEXTDOMAIN : 'extremis',
            get_stylesheet_directory() . '/languages'
        );
    }

    /**
     * Initializes the asset loader.
     *
     * @hook     after_setup_theme
     * @type     action
     * @priority 1
     */
    public function init_asset_loader() {
        ! empty( $this->assets )
        &&
        Asset_Loader::get_instance()->register_namespace( $this->namespace, $this->assets );
    }

    /**
     * Initialies widgets
     *
     * @hook widgets_init
     * @type action
     */
    public function init_widgets() {
        $widgets = array_filter(
            $this->get_dependencies(),
            fn( $d ) => str_contains( $d, 'Widget' )
        );

        array_walk( $widgets, 'register_widget' );
    }

    /**
     * Adds the current page slug to the body class.
     *
     * @param  string[] $classes Current body classes.
     * @return string[]          Modified body classes.
     *
     * @hook body_class
     * @type filter
     */
    public function modify_body_class( array $classes ): array {
        if ( is_single() || is_page() && ! is_front_page() ) {
            if ( ! in_array( basename( get_permalink() ), $classes, true ) ) {
                $classes[] = basename( get_permalink() );
            }
        }

        return array_filter( $classes );
    }
}
