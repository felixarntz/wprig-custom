<?php
/**
 * WP_Rig\WP_Rig\Customizer\Fonts class
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
 * Class for managing Customizer support for controlling colors.
 */
class Fonts {

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_filter( 'wp_rig_google_fonts', [ $this, 'filter_wprig_google_fonts' ], -999 );
		add_filter( 'wp_rig_preloading_styles_enabled', '__return_false' ); // Stylesheets must be included before custom properties.
		add_action( 'wp_head', [ $this, 'action_print_css_custom_properties_fonts' ] );
		add_action( 'customize_register', [ $this, 'action_customize_register' ] );
	}

	/**
	 * Filters the list of Google fonts to override fonts as provided by the Customizer settings.
	 *
	 * @param array $google_fonts List of Google fonts.
	 * @return array Filtered $google_fonts.
	 */
	public function filter_wprig_google_fonts( array $google_fonts ) {
		$google_fonts = [];

		$available_google_fonts = $this->get_available_google_fonts();
		$necessary_font_weights = [ '400', '500', '700', '900' ];

		$fonts = $this->get_fonts();
		foreach ( $fonts as $font_data ) {
			$value = get_theme_mod( $font_data['setting'], $font_data['default'] );

			if ( isset( $available_google_fonts[ $value ] ) && ! isset( $google_fonts[ $value ] ) ) {
				$font_variants = [];
				if ( isset( $available_google_fonts[ $value ]['weight'] ) ) {
					$font_weights = array_intersect( $available_google_fonts[ $value ]['weight'], $necessary_font_weights );
				} else {
					$font_weights = [ '400', '700' ];
				}
				foreach ( $font_weights as $font_weight ) {
					$font_variants[] = $font_weight;
					$font_variants[] = $font_weight . 'i';
				}

				$google_fonts[ $value ] = $font_variants;
			}
		}

		return $google_fonts;
	}

	/**
	 * Prints a style tag containing CSS rules for font custom properties controlled by the Customizer.
	 */
	public function action_print_css_custom_properties_fonts() {
		echo '<style id="wp-rig-css-custom-properties-fonts" type="text/css">';
		$this->print_css_custom_properties_fonts();
		echo '</style>';
	}

	/**
	 * Adds Customizer settings and controls for modifying fonts.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register( WP_Customize_Manager $wp_customize ) {
		$fonts = $this->get_fonts();

		$available_fonts = array_keys( array_merge( $this->get_available_system_fonts(), $this->get_available_google_fonts() ) );
		$available_fonts = array_combine( $available_fonts, $available_fonts );

		$wp_customize->add_section(
			'fonts',
			array(
				'title'    => __( 'Fonts', 'wp-rig' ),
				'priority' => 42,
			)
		);

		$partial_settings = [];
		foreach ( $fonts as $font_data ) {
			$wp_customize->add_setting(
				$font_data['setting'],
				[
					'default'   => $font_data['default'],
					'transport' => 'postMessage',
				]
			);

			$wp_customize->add_control(
				$font_data['setting'],
				[
					'type'    => 'select',
					'label'   => $font_data['title'],
					'section' => 'fonts',
					'choices' => $available_fonts,
				]
			);

			$partial_settings[] = $font_data['setting'];
		}

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'css_custom_properties_fonts',
				[
					'settings'        => $partial_settings,
					'selector'        => '#wp-rig-css-custom-properties-fonts',
					'render_callback' => function() {
						$this->print_css_custom_properties_fonts();
					},
				]
			);

			$wp_customize->selective_refresh->add_partial(
				'google_fonts',
				[
					'settings'            => $partial_settings,
					'selector'            => '#wp-rig-fonts-css',
					'container_inclusive' => true,
					'render_callback'     => function() {
						$url = wp_rig()->get_google_fonts_url();

						echo '<link rel="stylesheet" id="wp-rig-fonts-css" href="' . esc_url( $url ) . '" type="text/css" media="all" />'; // phpcs:ignore WordPress.WP.EnqueuedResources
					},
				]
			);
		}
	}

	/**
	 * Prints CSS rules for custom properties controlled by the Customizer.
	 */
	protected function print_css_custom_properties_fonts() {
		$fonts = $this->get_fonts();

		$available_fonts = array_merge( $this->get_available_system_fonts(), $this->get_available_google_fonts() );

		echo ':root{';
		foreach ( $fonts as $font_data ) {
			$value  = get_theme_mod( $font_data['setting'], $font_data['default'] );
			$output = $this->quote_font_family( $value );
			if ( isset( $available_fonts[ $value ]['fallback'] ) ) {
				$output .= ',' . implode( ',', array_map( [ $this, 'quote_font_family' ], $available_fonts[ $value ]['fallback'] ) );
			}

			echo '--' . esc_attr( $font_data['css_property'] ) . ':' . str_replace( '&quot;', '"', esc_attr( $output ) ) . ';'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '}';
	}

	/**
	 * Wraps a font family string into double quotes if it contains a space.
	 *
	 * @param string $font_family Font family.
	 * @return string Font family, possible in double quotes.
	 */
	protected function quote_font_family( $font_family ) {
		if ( strpos( $font_family, ' ' ) ) {
			$font_family = '"' . $font_family . '"';
		}

		return $font_family;
	}

	/**
	 * Gets fonts that can be modified in the Customizer.
	 *
	 * @return array List of fonts data.
	 */
	protected function get_fonts() {
		return [
			[
				'setting'      => 'global_font_family',
				'css_property' => 'global-font-family',
				'title'        => __( 'Global Font Family', 'wp-rig' ),
				'default'      => 'Crimson Text',
			],
			[
				'setting'      => 'branding_font_family',
				'css_property' => 'branding-font-family',
				'title'        => __( 'Branding Font Family', 'wp-rig' ),
				'default'      => 'Roboto Condensed',
			],
			[
				'setting'      => 'highlight_font_family',
				'css_property' => 'highlight-font-family',
				'title'        => __( 'Highlight Font Family', 'wp-rig' ),
				'default'      => 'Roboto Condensed',
			],
		];
	}

	/**
	 * Gets the available system fonts to choose from.
	 *
	 * @return array Associative array of $font_family => $font_data pairs.
	 */
	protected function get_available_system_fonts() {
		return [
			'Arial'           => [
				'fallback' => [ 'sans-serif' ],
			],
			'Georgia'         => [
				'fallback' => [ 'serif' ],
			],
			'Helvetica'       => [
				'fallback' => [ 'sans-serif' ],
			],
			'Times New Roman' => [
				'fallback' => [ 'serif' ],
			],
		];
	}

	/**
	 * Gets the available Google fonts to choose from.
	 *
	 * This list is curated via the CoBlocks plugin, plus it includes the two fonts that are used in WP Rig by default.
	 *
	 * @see https://github.com/godaddy/coblocks/blob/master/src/components/font-family/fonts.js
	 *
	 * @return array Associative array of $font_family => $font_data pairs.
	 */
	protected function get_available_google_fonts() {
		return [
			'Abril Fatface'      => [
				'weight'   => [ '400' ],
				'fallback' => [ 'serif' ],
			],
			'Anton'              => [
				'weight'   => [ '400' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Arvo'               => [
				'weight'   => [ '400', '700' ],
				'fallback' => [ 'serif' ],
			],
			'Asap'               => [
				'weight'   => [ '400', '500', '600', '700' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Barlow Condensed'   => [
				'weight'   => [ '100', '200', '300', '400', '500', '600', '700', '800', '900' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Barlow'             => [
				'weight'   => [ '100', '200', '300', '400', '500', '600', '700', '800', '900' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Cormorant Garamond' => [
				'weight'   => [ '300', '400', '500', '600', '700' ],
				'fallback' => [ 'serif' ],
			],
			'Crimson Text'       => [
				'weight'   => [ '400', '600', '700' ],
				'fallback' => [ 'serif' ],
			],
			'Faustina'           => [
				'weight'   => [ '400', '500', '600', '700' ],
				'fallback' => [ 'serif' ],
			],
			'Fira Sans'          => [
				'weight'   => [ '100', '200', '300', '400', '500', '600', '700', '800', '900' ],
				'fallback' => [ 'sans-serif' ],
			],
			'IBM Plex Sans'      => [
				'weight'   => [ '100', '200', '300', '400', '500', '600', '700' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Inconsolata'        => [
				'weight'   => [ '400', '700' ],
				'fallback' => [ 'monospace' ],
			],
			'Heebo'              => [
				'weight'   => [ '100', '200', '300', '400', '500', '600', '700', '800', '900' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Karla'              => [
				'weight'   => [ '400', '700' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Lato'               => [
				'weight'   => [ '100', '200', '300', '400', '500', '600', '700', '800', '900' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Lora'               => [
				'weight'   => [ '400', '700' ],
				'fallback' => [ 'serif' ],
			],
			'Merriweather'       => [
				'weight'   => [ '300', '400', '500', '600', '700', '800', '900' ],
				'fallback' => [ 'serif' ],
			],
			'Montserrat'         => [
				'weight'   => [ '100', '200', '300', '400', '500', '600', '700', '800', '900' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Noto Sans'          => [
				'weight'   => [ '400', '700' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Noto Serif'         => [
				'weight'   => [ '400', '700' ],
				'fallback' => [ 'serif' ],
			],
			'Open Sans'          => [
				'weight'   => [ '300', '400', '500', '600', '700', '800' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Oswald'             => [
				'weight'   => [ '200', '300', '400', '500', '600', '700' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Playfair Display'   => [
				'weight'   => [ '400', '700', '900' ],
				'fallback' => [ 'serif' ],
			],
			'PT Serif'           => [
				'weight'   => [ '400', '700' ],
				'fallback' => [ 'serif' ],
			],
			'Roboto Condensed'   => [
				'weight'   => [ '300', '400', '700' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Roboto'             => [
				'weight'   => [ '100', '300', '400', '500', '700', '900' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Rubik'              => [
				'weight'   => [ '300', '400', '500', '700', '900' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Tajawal'            => [
				'weight'   => [ '200', '300', '400', '500', '700', '800', '900' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Ubuntu'             => [
				'weight'   => [ '300', '400', '500', '700' ],
				'fallback' => [ 'sans-serif' ],
			],
			'Yrsa'               => [
				'weight'   => [ '300', '400', '500', '600', '700' ],
				'fallback' => [ 'serif' ],
			],
		];
	}
}
