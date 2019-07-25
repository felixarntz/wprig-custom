<?php
/**
 * WP_Rig\WP_Rig\Customizer\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Customizer;

use WP_Rig\WP_Rig\Component_Interface;
use function WP_Rig\WP_Rig\wp_rig;
use WP_Customize_Manager;
use function add_action;
use function bloginfo;
use function wp_enqueue_script;
use function get_theme_file_uri;

/**
 * Class for managing Customizer integration.
 */
class Component implements Component_Interface {

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'customizer';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_filter( 'wprig_editor_color_palette', [ $this, 'filter_wprig_editor_color_palette' ] );
		add_action( 'wp_head', [ $this, 'action_print_css_custom_properties' ] );
		add_action( 'customize_register', [ $this, 'action_customize_register' ] );
		add_action( 'customize_preview_init', [ $this, 'action_enqueue_customize_preview_js' ] );
	}

	/**
	 * Filters the editor color palette to override colors as provided by the Customizer settings.
	 *
	 * @param array $editor_color_palette List of custom theme color data sets.
	 * @return array Filtered $editor_color_palette.
	 */
	public function filter_wprig_editor_color_palette( array $editor_color_palette ) {
		$custom_theme_colors = $this->get_custom_theme_colors();
		$custom_color_values = [];
		foreach ( $custom_theme_colors as $color_data ) {
			$color_slug = str_replace( 'color-', '', $color_data['css_property'] );

			$custom_color_values[ $color_slug ] = get_theme_mod( $color_data['setting'], $color_data['default'] );
		}

		foreach ( $editor_color_palette as $index => $color_set ) {
			if ( ! isset( $custom_color_values[ $color_set['slug'] ] ) ) {
				continue;
			}

			$editor_color_palette[ $index ]['color'] = $custom_color_values[ $color_set['slug'] ];
		}

		return $editor_color_palette;
	}

	/**
	 * Prints a style tag containing CSS rules for custom properties controlled by the Customizer.
	 */
	public function action_print_css_custom_properties() {
		echo '<style id="wprig-css-custom-properties" type="text/css">';
		$this->print_css_custom_properties();
		echo '</style>';
	}

	/**
	 * Adds postMessage support for site title and description, plus a custom Theme Options section.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register( WP_Customize_Manager $wp_customize ) {
		$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'blogname',
				[
					'selector'        => '.site-title a',
					'render_callback' => function() {
						bloginfo( 'name' );
					},
				]
			);
			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				[
					'selector'        => '.site-description',
					'render_callback' => function() {
						bloginfo( 'description' );
					},
				]
			);
		}

		/**
		 * Theme options.
		 */
		$wp_customize->add_section(
			'theme_options',
			[
				'title'    => __( 'Theme Options', 'wp-rig' ),
				'priority' => 130, // Before Additional CSS.
			]
		);

		$this->customize_register_color_settings( $wp_customize );
	}

	/**
	 * Enqueues JavaScript to make Customizer preview reload changes asynchronously.
	 */
	public function action_enqueue_customize_preview_js() {
		wp_enqueue_script(
			'wp-rig-customizer',
			get_theme_file_uri( '/assets/js/customizer.min.js' ),
			[ 'customize-preview' ],
			wp_rig()->get_asset_version( get_theme_file_path( '/assets/js/customizer.min.js' ) ),
			true
		);
	}

	/**
	 * Adds Customizer settings and controls for modifying colors.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	protected function customize_register_color_settings( WP_Customize_Manager $wp_customize ) {
		$colors = array_merge( $this->get_base_colors(), $this->get_custom_theme_colors() );

		$partial_settings = [];
		foreach ( $colors as $color_data ) {
			$wp_customize->add_setting(
				$color_data['setting'],
				[
					'default'              => $color_data['default'],
					'transport'            => 'postMessage',
					'sanitize_callback'    => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color',
				]
			);

			$wp_customize->add_control(
				new \WP_Customize_Color_Control(
					$wp_customize,
					$color_data['setting'],
					[
						'label'   => $color_data['title'],
						'section' => 'colors',
					]
				)
			);

			$partial_settings[] = $color_data['setting'];
		}

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'css_custom_properties',
				[
					'settings'        => $partial_settings,
					'selector'        => '#wprig-css-custom-properties',
					'render_callback' => function() {
						$this->print_css_custom_properties();
					},
				]
			);
		}
	}

	/**
	 * Prints CSS rules for custom properties controlled by the Customizer.
	 */
	protected function print_css_custom_properties() {
		$colors = array_merge( $this->get_base_colors(), $this->get_custom_theme_colors() );

		echo ':root{';
		foreach ( $colors as $color_data ) {
			$value = get_theme_mod( $color_data['setting'], $color_data['default'] );

			echo '--' . esc_attr( $color_data['css_property'] ) . ':' . esc_attr( $value ) . ';';
		}
		echo '}';
	}

	/**
	 * Gets base colors that can be modified in the Customizer.
	 *
	 * @return array List of base colors data.
	 */
	protected function get_base_colors() {
		return [
			[
				'setting'      => 'global_font_color',
				'css_property' => 'global-font-color',
				'title'        => __( 'Global Font Color', 'wp-rig' ),
				'default'      => '#333333',
			],
			[
				'setting'      => 'color_link',
				'css_property' => 'color-link',
				'title'        => __( 'Link Color', 'wp-rig' ),
				'default'      => '#0073aa',
			],
			[
				'setting'      => 'color_link_visited',
				'css_property' => 'color-link-visited',
				'title'        => __( 'Link Visited Color', 'wp-rig' ),
				'default'      => '#333333',
			],
			[
				'setting'      => 'color_link_active',
				'css_property' => 'color-link-active',
				'title'        => __( 'Link Active Color', 'wp-rig' ),
				'default'      => '#00a0d2',
			],
		];
	}

	/**
	 * Gets custom theme colors that can be modified in the Customizer and are also available in the block editor.
	 *
	 * @return array List of custom theme colors data.
	 */
	protected function get_custom_theme_colors() {
		return [
			[
				'setting'      => 'color_theme_primary',
				'css_property' => 'color-theme-primary',
				'title'        => __( 'Primary Color', 'wp-rig' ),
				'default'      => '#e36d60',
			],
			[
				'setting'      => 'color_theme_secondary',
				'css_property' => 'color-theme-secondary',
				'title'        => __( 'Secondary Color', 'wp-rig' ),
				'default'      => '#41848f',
			],
			[
				'setting'      => 'color_theme_red',
				'css_property' => 'color-theme-red',
				'title'        => __( 'Red Color', 'wp-rig' ),
				'default'      => '#c0392b',
			],
			[
				'setting'      => 'color_theme_green',
				'css_property' => 'color-theme-green',
				'title'        => __( 'Green Color', 'wp-rig' ),
				'default'      => '#27ae60',
			],
			[
				'setting'      => 'color_theme_blue',
				'css_property' => 'color-theme-blue',
				'title'        => __( 'Blue Color', 'wp-rig' ),
				'default'      => '#2980b9',
			],
			[
				'setting'      => 'color_theme_yellow',
				'css_property' => 'color-theme-yellow',
				'title'        => __( 'Yellow Color', 'wp-rig' ),
				'default'      => '#f1c40f',
			],
			[
				'setting'      => 'color_theme_black',
				'css_property' => 'color-theme-black',
				'title'        => __( 'Black Color', 'wp-rig' ),
				'default'      => '#1c2833',
			],
			[
				'setting'      => 'color_theme_grey',
				'css_property' => 'color-theme-grey',
				'title'        => __( 'Grey Color', 'wp-rig' ),
				'default'      => '#95a5a6',
			],
			[
				'setting'      => 'color_theme_white',
				'css_property' => 'color-theme-white',
				'title'        => __( 'White Color', 'wp-rig' ),
				'default'      => '#ecf0f1',
			],
		];
	}
}
