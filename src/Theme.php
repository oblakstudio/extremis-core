<?php
/**
 * Theme class file.
 *
 * @package Extremis
 */

namespace XWP\Extremis;

use XWP\DI\Decorators\Action;
use XWP\DI\Decorators\Filter;
use XWP\Extremis\Decorators\Theme_Module;

/**
 * Extremis Theme Module.
 */
#[Theme_Module]
class Theme {
    /**
     * Get the module definition.
     *
     * @return array<string,mixed>
     */
    public static function configure(): array {
        $definition = \locate_template( '/config/definition.php' );

        return $definition
            ? require $definition
            : array();
    }

    /**
     * Loads the textdomain
     *
     * @param  \DI\Container $cnt Container. Injected.
     */
    #[Action(
        tag: 'after_setup_theme',
        priority: 1,
        invoke: Action::INV_PROXIED,
        args: 0,
        params: array(
            \DI\Container::class,
        ),
    )]
    public function load_textdomain( \DI\Container $cnt ): void {
        \load_child_theme_textdomain(
            $cnt->get( 'cfg.textdomain' ),
            \get_stylesheet_directory() . '/languages',
        );
    }

    /**
     * Enqueue assets
     *
     * @param  \DI\Container $cnt Container. Injected.
     */
    #[Action(
        tag: 'after_setup_theme',
        priority: 1,
        context: Action::CTX_FRONTEND | Action::CTX_ADMIN,
        invoke: Action::INV_PROXIED,
        args: 0,
        params: array(
            \DI\Container::class,
        ),
    )]
    public function enqueue_assets( \DI\Container $cnt ): void {
        if ( ! $cnt->has( 'cfg.assets' ) ) {
            return;
        }
        $bundle             = $cnt->get( 'cfg.assets' );
        $bundle['base_uri'] = \get_theme_file_uri( '/dist' );
        $bundle['base_dir'] = \get_theme_file_path( '/dist' );
        $bundle['id']     ??= 'extremis';
        $bundle['manifest'] = 'assets.php';
        $bundle['version']  = $cnt->get( 'cfg.version' );

        $cnt->set(
            \XWP_Asset_Bundle::class,
            \XWP_Asset_Loader::load_bundle( $bundle )->get_bundle( $bundle['id'] ),
        );
    }

    /**
     * Add custom body classes
     *
     * @param  array<string> $classes Body classes.
     * @return array<string>
     */
    #[Filter( tag: 'body_class', priority: 10, context: Filter::CTX_FRONTEND )]
    public function change_body_class( array $classes ): array {
        if ( ( \is_single() || \is_page() ) && ! \is_front_page() ) {
            $page_class = \basename( \get_permalink() );

            if ( ! \in_array( $page_class, $classes, true ) ) {
                $classes[] = $page_class;
            }
        }

        return \array_filter( $classes );
    }
}
