<?php
/**
 * Theme Module decorator.
 *
 * @package Extremis
 * @subpackage Decorators
 */

namespace XWP\Extremis\Decorators;

use XWP\DI\Decorators\Module;

/**
 * Theme Module decorator.
 *
 * @template T of object
 * @extends Module<T>
 */
#[\Attribute( \Attribute::TARGET_CLASS )]
class Theme_Module extends Module {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            container: 'extremis',
            hook: 'after_setup_theme',
            priority: -1000,
            extendable: true,
        );
    }

    /**
     * Set the handlers.
     */
    protected function set_handlers(): void {
        $this->handlers = \apply_filters( 'xwp_extend_handlers_extremis', $this->handlers );
    }

    /**
     * Extend the module definition.
     *
     * @return array<mixed>
     */
    protected function extend_definition(): array {
        return \apply_filters( 'xwp_extend_config_extremis', array() );
    }

    /**
     * Initialize the module.
     *
     * @return bool
     */
    public function on_initialize(): bool {
        $this->set_handlers();

        return parent::on_initialize();
    }

    /**
     * Get the module definition.
     *
     * @return array<mixed>
     */
    public function get_definition(): array {
        return \array_merge(
            parent::get_definition(),
            $this->extend_definition(),
        );
    }
}
