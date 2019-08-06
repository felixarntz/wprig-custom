<?php
/**
 * WP_Rig\WP_Rig\Customizer\Colors class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Customizer;

use WP_Customize_Manager;
use function add_filter;
use function add_action;
use function get_theme_mod;

/**
 * Class for managing Customizer support for controlling colors.
 */
class Colors {

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_filter( 'wprig_editor_color_palette', [ $this, 'filter_wprig_editor_color_palette' ] );
		add_filter( 'wp_rig_preloading_styles_enabled', '__return_false' ); // Stylesheets must be included before custom properties.
		add_action( 'wp_head', [ $this, 'action_print_css_custom_properties_colors' ] );
		add_action( 'customize_register', [ $this, 'action_customize_register' ] );
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
	 * Prints a style tag containing CSS rules for color custom properties controlled by the Customizer.
	 */
	public function action_print_css_custom_properties_colors() {
		echo '<style id="wp-rig-css-custom-properties-colors" type="text/css">';
		$this->print_css_custom_properties_colors();
		echo '</style>';
	}

	/**
	 * Adds Customizer settings and controls for modifying colors.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register( WP_Customize_Manager $wp_customize ) {
		$colors = array_merge( $this->get_base_colors(), $this->get_custom_theme_colors() );

		$partial_settings = [];
		if ( current_theme_supports( 'custom-background' ) ) { // Replaces the component's own background color setting.
			$partial_settings[] = 'background_color';
		}
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
				'css_custom_properties_colors',
				[
					'settings'        => $partial_settings,
					'selector'        => '#wp-rig-css-custom-properties-colors',
					'render_callback' => function() {
						$this->print_css_custom_properties_colors();
					},
				]
			);
		}
	}

	/**
	 * Prints CSS rules for custom properties controlled by the Customizer.
	 */
	protected function print_css_custom_properties_colors() {
		$colors = array_merge( $this->get_base_colors(), $this->get_custom_theme_colors() );

		echo ':root{';
		if ( current_theme_supports( 'custom-background' ) && get_background_color() ) { // Replaces the component's own background color setting.
			echo '--global-background-color:#' . esc_attr( get_background_color() ) . ';';
		}
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
		$colors = [
			[
				'setting'      => 'global_background_color', // Only used if no custom background support.
				'css_property' => 'global-background-color',
				'title'        => __( 'Background Color', 'wp-rig' ),
				'default'      => '#ffffff',
			],
			[
				'setting'      => 'global_background_color_alt',
				'css_property' => 'global-background-color-alt',
				'title'        => __( 'Background Color (alt.)', 'wp-rig' ),
				'default'      => '#eeeeee',
			],
			[
				'setting'      => 'global_font_color',
				'css_property' => 'global-font-color',
				'title'        => __( 'Font Color', 'wp-rig' ),
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
			[
				'setting'      => 'branding_font_color',
				'css_property' => 'branding-font-color',
				'title'        => __( 'Branding Font Color', 'wp-rig' ),
				'default'      => '#333333',
			],
			[
				'setting'      => 'highlight_font_color',
				'css_property' => 'highlight-font-color',
				'title'        => __( 'Highlight Font Color', 'wp-rig' ),
				'default'      => '#333333',
			],
			[
				'setting'      => 'global_border_color',
				'css_property' => 'global-border-color',
				'title'        => __( 'Border Color', 'wp-rig' ),
				'default'      => '#cccccc',
			],
		];

		if ( current_theme_supports( 'custom-background' ) ) {
			array_shift( $colors );
		}

		return $colors;
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
