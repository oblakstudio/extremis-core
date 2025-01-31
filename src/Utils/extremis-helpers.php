<?php //phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
/**
 * Helper functions.
 *
 * @package Extremis
 * @subpackage Utils
 */

use XWP\Extremis\Builder;

/**
 * Initialize the eXtremis application.
 *
 * @param  array<int,class-string> $modules    Array of submodules to import.
 * @param  array<int,class-string> $handlers   Array of handlers to register.
 * @param  string                  $textdomain Textdomain.
 * @param  string                  $version    Version.
 */
function extremis_init(
    array $modules = array(),
    array $handlers = array(),
    string $textdomain = 'extremis',
    string $version = '0.0.0',
) {
    Builder::build( $modules, $handlers, $textdomain, $version );
}

/**
 * Get the eXtremis container.
 *
 * @return DI\Container
 */
function Extremis(): DI\Container {
    return xwp_app( 'extremis' );
}
