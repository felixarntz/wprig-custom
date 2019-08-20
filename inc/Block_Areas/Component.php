<?php
/**
 * WP_Rig\WP_Rig\Block_Areas\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Block_Areas;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use function add_action;
use function add_theme_support;

/**
 * Class for supporting the experimental Block Areas plugin.
 *
 * @see https://wordpress.org/plugins/block-areas/
 *
 * Exposes template tags:
 * * `wp_rig()->render_block_area( $slug )`
 * * `wp_rig()->has_block_area( $slug )`
 */
class Component implements Component_Interface, Templating_Component_Interface {

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'block_areas';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_action( 'after_setup_theme', [ $this, 'add_theme_support' ] );
		add_filter( 'wp_rig_css_files', [ $this, 'filter_wp_rig_css_files' ] );
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
			'render_block_area' => [ $this, 'render_block_area' ],
			'has_block_area'    => [ $this, 'has_block_area' ],
		];
	}

	/**
	 * Adds theme support for the Block Areas plugin.
	 */
	public function add_theme_support() {
		add_theme_support( 'block-areas', 'header', 'footer' );
	}

	/**
	 * Filters the CSS files to potentially enqueue.
	 *
	 * @param array $css_files Associative array of CSS files, as $handle => $data pairs.
	 * @return array Filtered $css_files.
	 */
	public function filter_wp_rig_css_files( array $css_files ) : array {
		// Skip footer widget areas style if not used because of block area.
		if ( isset( $css_files['wp-rig-footer-widget-areas'] ) && $this->has_block_area( 'footer' ) ) {
			unset( $css_files['wp-rig-footer-widget-areas'] );
		}

		return $css_files;
	}

	/**
	 * Renders the block area identified by the given slug, if it exists.
	 *
	 * @param string $slug Block area slug.
	 */
	public function render_block_area( string $slug ) {
		if ( ! function_exists( 'block_areas' ) ) {
			return;
		}

		\block_areas()->render( $slug );
	}

	/**
	 * Checks whether there is a block area identified by the given slug.
	 *
	 * @param string $slug Block area slug.
	 * @return bool True if there is a block area of that slug, false otherwise.
	 */
	public function has_block_area( string $slug ) : bool {
		if ( ! function_exists( 'block_areas' ) ) {
			return false;
		}

		return \block_areas()->exists( $slug );
	}
}
