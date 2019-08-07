<?php
/**
 * WP_Rig\WP_Rig\Customizer\Footer_Info class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Customizer;

use function WP_Rig\WP_Rig\wp_rig;
use WP_Customize_Manager;
use function add_filter;
use function add_action;
use function get_theme_mod;

/**
 * Class for managing Customizer support for modifying the footer info message.
 */
class Footer_Info {

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_filter( 'wp_rig_footer_info', [ $this, 'filter_wp_rig_footer_info' ] );
		add_action( 'customize_register', [ $this, 'action_customize_register' ] );
	}

	/**
	 * Filters the footer info message.
	 *
	 * @param string $footer_info The footer info message.
	 * @return string Filtered value of $footer_info.
	 */
	public function filter_wp_rig_footer_info( string $footer_info ) {
		return (string) get_theme_mod( 'footer_info', '' );
	}

	/**
	 * Adds Customizer settings and controls for modifying the footer info message.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_setting(
			'footer_info',
			[
				'default'   => '',
				'transport' => 'postMessage',
			]
		);
		$wp_customize->add_control(
			'footer_info',
			[
				'type'        => 'textarea',
				'label'       => __( 'Footer Info', 'wp-rig' ),
				'description' => __( 'You can change the credit message displayed in the site footer here. Basic HTML tags are allowed.', 'wp-rig' ),
				'section'     => 'title_tagline',
				'priority'    => 80,
			]
		);

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'footer_info',
				[
					'settings'            => [ 'footer_info' ],
					'selector'            => '.site-footer-info',
					'container_inclusive' => true,
					'render_callback'     => function() {
						wp_rig()->display_footer_info();
					},
				]
			);
		}
	}
}
