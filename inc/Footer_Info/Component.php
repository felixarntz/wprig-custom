<?php
/**
 * WP_Rig\WP_Rig\Footer_Info\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Footer_Info;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use function apply_filters;
use function esc_html__;
use function wp_kses;

/**
 * Class for managing a footer info message (e.g. copyright).
 *
 * Exposes template tags:
 * * `wp_rig()->display_footer_info()`
 */
class Component implements Component_Interface, Templating_Component_Interface {

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'footer_info';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		// Empty method body.
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
			'display_footer_info' => [ $this, 'display_footer_info' ],
		];
	}

	/**
	 * Displays the footer info message.
	 */
	public function display_footer_info() {
		$default_footer_info = '<a href="' . esc_url( __( 'https://wordpress.org/', 'wp-rig' ) ) . '">';
		/* translators: %s: CMS name, i.e. WordPress. */
		$default_footer_info .= sprintf( esc_html__( 'Proudly powered by %s', 'wp-rig' ), 'WordPress' );
		$default_footer_info .= '</a>';
		$default_footer_info .= '<span class="sep"> | </span>';
		/* translators: Theme name. */
		$default_footer_info .= sprintf( esc_html__( 'Theme: %s', 'wp-rig' ), '<a href="' . esc_url( 'https://github.com/wprig/wprig/' ) . '">WP Rig</a>' );

		$footer_info = $default_footer_info;

		/**
		 * Filters the footer info message.
		 *
		 * Basic inline HTML tags are allowed.
		 *
		 * @param string $footer_info The footer info message.
		 */
		$footer_info = (string) apply_filters( 'wp_rig_footer_info', $footer_info );

		if ( empty( $footer_info ) ) {
			$footer_info = $default_footer_info;
		}

		echo '<span class="site-footer-info">' . wp_kses( $footer_info, 'footer_info' ) . '</span>';
	}
}
