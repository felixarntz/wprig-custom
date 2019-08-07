<?php
/**
 * WP_Rig\WP_Rig\Customizer\Front_Page class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Customizer;

use function WP_Rig\WP_Rig\wp_rig;
use WP_Customize_Manager;
use WP_Post;
use function add_filter;
use function add_action;
use function get_option;
use function get_theme_mod;

/**
 * Class for managing Customizer support for toggling the post header and footer on the front page.
 */
class Front_Page {

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_filter( 'wp_rig_showing_post_header', [ $this, 'filter_wp_rig_showing_post_header' ], 10, 2 );
		add_filter( 'wp_rig_showing_post_footer', [ $this, 'filter_wp_rig_showing_post_footer' ], 10, 2 );
		add_action( 'customize_register', [ $this, 'action_customize_register' ] );
		add_action( 'customize_controls_enqueue_scripts', [ $this, 'action_customize_controls_enqueue_scripts' ] );
	}

	/**
	 * Filters whether the post header should be displayed.
	 *
	 * @param bool    $show Whether to display the header.
	 * @param WP_Post $post WordPress post object.
	 * @return bool Filtered value of $show.
	 */
	public function filter_wp_rig_showing_post_header( bool $show, WP_Post $post ) {
		if ( 'page' !== get_option( 'show_on_front' ) || (int) get_option( 'page_on_front' ) !== (int) $post->ID ) {
			return $show;
		}

		return ! get_theme_mod( 'hide_page_on_front_header' );
	}

	/**
	 * Filters whether the post footer should be displayed.
	 *
	 * @param bool    $show Whether to display the footer.
	 * @param WP_Post $post WordPress post object.
	 * @return bool Filtered value of $show.
	 */
	public function filter_wp_rig_showing_post_footer( bool $show, WP_Post $post ) {
		if ( 'page' !== get_option( 'show_on_front' ) || (int) get_option( 'page_on_front' ) !== (int) $post->ID ) {
			return $show;
		}

		return ! get_theme_mod( 'hide_page_on_front_footer' );
	}

	/**
	 * Adds Customizer settings and controls for modifying the footer info message.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_setting(
			'hide_page_on_front_header',
			[
				'default'              => false,
				'sanitize_callback'    => 'rest_sanitize_boolean',
				'sanitize_js_callback' => 'rest_sanitize_boolean',
			]
		);
		$wp_customize->add_control(
			'hide_page_on_front_header',
			[
				'type'        => 'checkbox',
				'label'       => __( 'Hide post header for front page?', 'wp-rig' ),
				'description' => __( 'If checked, the front page title and meta information will be hidden.', 'wp-rig' ),
				'section'     => 'static_front_page',
			]
		);

		$wp_customize->add_setting(
			'hide_page_on_front_footer',
			[
				'default'              => false,
				'sanitize_callback'    => 'rest_sanitize_boolean',
				'sanitize_js_callback' => 'rest_sanitize_boolean',
			]
		);
		$wp_customize->add_control(
			'hide_page_on_front_footer',
			[
				'type'        => 'checkbox',
				'label'       => __( 'Hide post footer for front page?', 'wp-rig' ),
				'description' => __( 'If checked, the front page terms and edit link will be hidden.', 'wp-rig' ),
				'section'     => 'static_front_page',
			]
		);
	}

	/**
	 * Adds Customizer inline script so that visibility for the custom controls is toggled based on the 'show_on_front' setting.
	 */
	public function action_customize_controls_enqueue_scripts() {
		$callback = "var callback = function( to ) { control.container.toggle( 'page' === to ); };";
		$binding  = "$.each( [ 'hide_page_on_front_header', 'hide_page_on_front_footer' ], function( i, controlId ) { wp.customize.control( controlId, function( control ) { " . $callback . ' callback( setting.get() ); setting.bind( callback ); } ); } );';
		$script   = "jQuery( function( $ ) { wp.customize( 'show_on_front', function( setting ) { " . $binding . ' } ); } );';

		wp_add_inline_script( 'customize-controls', $script, 'after' );
	}
}
