<?php //phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
/**
 * Helper functions.
 *
 * @package Extremis
 * @subpackage Utils
 */

use Extremis\Extremis;

/**
 * Returns the main instance of Extremis.
 *
 * @return Extremis
 */
function Extremis(): Extremis {
    return Extremis::get_instance();
}
