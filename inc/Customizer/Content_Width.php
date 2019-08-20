<?php
/**
 * WP_Rig\WP_Rig\Customizer\Content_Width class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Customizer;

use WP_Customize_Manager;
use function add_filter;
use function add_action;
use function get_theme_mod;

/**
 * Class for managing Customizer support for controlling the content width.
 */
class Content_Width {

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_filter( 'wp_rig_preloading_styles_enabled', '__return_false' ); // Stylesheets must be included before custom properties.
		add_filter( 'block_editor_settings', [ $this, 'filter_block_editor_settings_custom_properties_content_width' ] );
		add_action( 'wp_head', [ $this, 'action_print_css_custom_properties_content_width' ], 8 );
		add_action( 'customize_register', [ $this, 'action_customize_register' ] );
	}

	/**
	 * Filters the settings to pass to the block editor for adding content width custom properties controlled by the Customizer.
	 *
	 * @param array $editor_settings Editor settings.
	 * @return array Filtered value of $editor_settings.
	 */
	public function filter_block_editor_settings_custom_properties_content_width( array $editor_settings ) {
		ob_start();
		$this->print_css_custom_properties_content_width();

		$editor_settings['styles'][] = [
			'css' => ob_get_clean(),
		];

		return $editor_settings;
	}

	/**
	 * Prints a style tag containing CSS rules for content width custom properties controlled by the Customizer.
	 */
	public function action_print_css_custom_properties_content_width() {
		echo '<style id="wp-rig-css-custom-properties-content-width" type="text/css">';
		$this->print_css_custom_properties_content_width();
		echo '</style>';
	}

	/**
	 * Adds Customizer settings and controls for modifying colors.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_setting(
			'content_width',
			[
				'default'              => 45,
				'transport'            => 'postMessage',
				'sanitize_callback'    => 'absint',
				'sanitize_js_callback' => 'absint',
			]
		);

		$wp_customize->add_control(
			'content_width',
			[
				'type'     => 'range',
				'label'    => __( 'Maximum content width', 'wp-rig' ),
				'section'  => 'theme_options',
				'priority' => 5,
				'min'      => 32,
				'max'      => 70,
				'step'     => 1,
			]
		);

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'css_custom_properties_content_width',
				[
					'settings'        => [ 'content_width' ],
					'selector'        => '#wp-rig-css-custom-properties-content-width',
					'render_callback' => function() {
						$this->print_css_custom_properties_content_width();
					},
				]
			);
		}
	}

	/**
	 * Prints CSS rules for custom properties controlled by the Customizer.
	 */
	protected function print_css_custom_properties_content_width() {
		$content_width = (int) get_theme_mod( 'content_width', 45 );
		if ( ! $content_width ) {
			$content_width = 45;
		}

		echo ':root{--content-width:' . esc_attr( '' . $content_width . 'rem' ) . ';}';
	}
}
