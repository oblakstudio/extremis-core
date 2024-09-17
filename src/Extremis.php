<?php
/**
 * Extremis class file.
 *
 * @package Extremis
 */

namespace Oblak\WP;

use Automattic\Jetpack\Constants;
use Oblak\WP\Asset_Loader;
use Oblak\WP\Decorators\Action;
use Oblak\WP\Decorators\Filter;
use Oblak\WP\Loader_Trait;
use Oblak\WP\Traits\Hook_Processor_Trait;
use XWP\Helper\Traits\Singleton;

/**
 * Main child theme class
 */
class Extremis {
    use Singleton;
    use \XWP_Asset_Retriever;
    use Hook_Processor_Trait;

    /**
     * Asset config array
     *
     * @var array<string, mixed>|false
     */
    private array|bool $assets;

    /**
     * Class constructor
     */
    private function __construct() {
        $this->bundle_id = $this->get_namespace();
        $this->assets    = $this->get_assets();

        $this->init( 'after_setup_theme', -1 );
    }

    /**
     * Returns the theme namespace
     *
     * @return string
     */
    protected function get_namespace(): string {
        return Constants::get_constant( 'EXTREMIS_NAMESPACE' ) ?? 'extremis';
    }

    /**
     * Returns the asset config array
     *
     * @return array<string,mixed>|false
     */
    protected function get_assets(): array|bool {
        $file = \locate_template( '/config/assets.php' );

        if ( ! $file ) {
            return false;
        }

        return \xwp_array_diff_assoc(
            \array_merge( require $file, array( 'id' => $this->bundle_id ) ),
            'namespace',
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function get_dependencies(): array {
        return require \locate_template( '/config/dependencies.php' );
    }

    /**
     * {@inheritDoc}
     */
    public function run_hooks() {
        \xwp_invoke_hooked_methods( $this );
    }

    /**
     * Loads the textdomain
     */
    #[Action( tag: 'after_setup_theme', priority: 1 )]
    public function load_textdomain() {
        \load_child_theme_textdomain(
            Constants::get_constant( 'EXTREMIS_TEXTDOMAIN' ) ?? 'extremis',
            \get_stylesheet_directory() . '/languages',
        );
    }

    /**
     * Initializes the asset loader.
     */
    #[Action( tag: 'after_setup_theme', priority: 1 )]
    public function init_asset_loader() {
        if ( ! $this->assets ) {
            return;
        }

        ! isset( $this->assets['base_dir'] )
            ? Asset_Loader::get_instance()->register_namespace( $this->bundle_id, $this->assets )
            : \XWP_Asset_Loader::load_bundle( $this->assets );
    }

    /**
     * Initialies widgets
     */
    #[Action( tag: 'widgets_init', priority: 10 )]
    public function init_widgets() {
        foreach ( $this->get_dependencies() as $dep ) {
            if ( ! \str_contains( $dep, 'Widget' ) ) {
                continue;
            }

            \register_widget( $dep );
        }
    }

    /**
     * Adds the current page slug to the body class.
     *
     * @param  string[] $classes Current body classes.
     * @return string[]          Modified body classes.
     */
    #[Filter( tag: 'body_class', priority: 10 )]
    public function modify_body_class( array $classes ): array {
        if ( \is_single() || \is_page() && ! \is_front_page() ) {
            if ( ! \in_array( \basename( \get_permalink() ), $classes, true ) ) {
                $classes[] = \basename( \get_permalink() );
            }
        }

        return \array_filter( $classes );
    }
}
