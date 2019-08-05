<?php
/**
 * WP_Rig\WP_Rig\Footer_Widget_Areas\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Footer_Widget_Areas;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use function add_action;
use function register_sidebar;
use function esc_html;
use function esc_html__;
use function is_active_sidebar;
use function dynamic_sidebar;

/**
 * Class for managing footer widget areas.
 *
 * Exposes template tags:
 * * `wp_rig()->get_footer_widget_area_count()`
 * * `wp_rig()->has_active_footer_widget_areas()`
 * * `wp_rig()->is_footer_widget_area_active( int $column )`
 * * `wp_rig()->display_footer_widget_area( int $column )`
 */
class Component implements Component_Interface, Templating_Component_Interface {

	const WIDGET_AREA_COUNT = 4;

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'footer_widget_areas';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_action( 'widgets_init', [ $this, 'action_register_footer_widget_areas' ] );
	}

	/**
	 * Gets template tags to expose as methods on the Template_Tags class instance, accessible through `wp_rig()`.
	 *
	 * @return array Associative array of $method_name => $callback_info pairs. Each $callback_info must either be
	 *               a callable or an array with key 'callable'. This approach is used to reserve the possibility of
	 *               adding support for further arguments in the future.
	 */
	public function template_tags() : array {
		return [
			'get_footer_widget_area_count'   => [ $this, 'get_footer_widget_area_count' ],
			'has_active_footer_widget_areas' => [ $this, 'has_active_footer_widget_areas' ],
			'is_footer_widget_area_active'   => [ $this, 'is_footer_widget_area_active' ],
			'display_footer_widget_area'     => [ $this, 'display_footer_widget_area' ],
		];
	}

	/**
	 * Registers the footer widget areas.
	 */
	public function action_register_footer_widget_areas() {
		for ( $column = 1; $column <= static::WIDGET_AREA_COUNT; $column++ ) {
			register_sidebar(
				[
					/* translators: %s: column number */
					'name'          => esc_html( sprintf( __( 'Footer Column %s', 'wp-rig' ), $column ) ),
					'id'            => sprintf( 'footer-%s', $column ),
					'description'   => esc_html__( 'Add widgets here to appear in the footer.', 'wp-rig' ),
					'before_widget' => '<section id="%1$s" class="widget %2$s">',
					'after_widget'  => '</section>',
					'before_title'  => '<h2 class="widget-title">',
					'after_title'   => '</h2>',
				]
			);
		}
	}

	/**
	 * Gets the number of available footer widget areas.
	 *
	 * @return int Number of footer widget area columns.
	 */
	public function get_footer_widget_area_count() : int {
		return static::WIDGET_AREA_COUNT;
	}

	/**
	 * Checks whether there is at least one active footer widget area.
	 *
	 * @return bool True if at least one footer widget area is active.
	 */
	public function has_active_footer_widget_areas() : bool {
		for ( $column = 1; $column <= static::WIDGET_AREA_COUNT; $column++ ) {
			if ( $this->is_footer_widget_area_active( $column ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks whether the given footer widget area is active.
	 *
	 * @param int $column Footer widget column number (between 1 and 4).
	 * @return bool True if the given footer widget area is active, false otherwise.
	 */
	public function is_footer_widget_area_active( int $column ) : bool {
		return (bool) is_active_sidebar( 'footer-' . $column );
	}

	/**
	 * Displays the given footer widget area.
	 *
	 * @param int $column Footer widget column number (between 1 and 4).
	 */
	public function display_footer_widget_area( int $column ) {
		dynamic_sidebar( 'footer-' . $column );
	}
}
