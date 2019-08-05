<?php
/**
 * WP_Rig\WP_Rig\Editor\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Editor;

use WP_Rig\WP_Rig\Component_Interface;
use function add_action;
use function add_theme_support;

/**
 * Class for integrating with the block editor.
 *
 * @link https://wordpress.org/gutenberg/handbook/extensibility/theme-support/
 */
class Component implements Component_Interface {

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'editor';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_action( 'after_setup_theme', [ $this, 'action_add_editor_support' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'action_add_block_style_variations' ] );
	}

	/**
	 * Adds support for various editor features.
	 */
	public function action_add_editor_support() {
		// Add support for editor styles.
		add_theme_support( 'editor-styles' );

		// Add support for default block styles.
		add_theme_support( 'wp-block-styles' );

		// Add support for wide-aligned images.
		add_theme_support( 'align-wide' );

		$editor_color_palette = [
			[
				'name'  => _x( 'Primary', 'color', 'wp-rig' ),
				'slug'  => 'theme-primary',
				'color' => '#e36d60',
			],
			[
				'name'  => _x( 'Secondary', 'color', 'wp-rig' ),
				'slug'  => 'theme-secondary',
				'color' => '#41848f',
			],
			[
				'name'  => __( 'Red', 'wp-rig' ),
				'slug'  => 'theme-red',
				'color' => '#C0392B',
			],
			[
				'name'  => __( 'Green', 'wp-rig' ),
				'slug'  => 'theme-green',
				'color' => '#27AE60',
			],
			[
				'name'  => __( 'Blue', 'wp-rig' ),
				'slug'  => 'theme-blue',
				'color' => '#2980B9',
			],
			[
				'name'  => __( 'Yellow', 'wp-rig' ),
				'slug'  => 'theme-yellow',
				'color' => '#F1C40F',
			],
			[
				'name'  => __( 'Black', 'wp-rig' ),
				'slug'  => 'theme-black',
				'color' => '#1C2833',
			],
			[
				'name'  => __( 'Grey', 'wp-rig' ),
				'slug'  => 'theme-grey',
				'color' => '#95A5A6',
			],
			[
				'name'  => __( 'White', 'wp-rig' ),
				'slug'  => 'theme-white',
				'color' => '#ECF0F1',
			],
		];

		/**
		 * Filters the list of custom theme colors to make available in the block editor.
		 *
		 * @param array $editor_color_palette List of custom theme color data sets.
		 */
		$editor_color_palette = apply_filters( 'wprig_editor_color_palette', $editor_color_palette );

		/**
		 * Add support for color palettes.
		 *
		 * To preserve color behavior across themes, use these naming conventions:
		 * - Use primary and secondary color for main variations.
		 * - Use `theme-[color-name]` naming standard for standard colors (red, blue, etc).
		 * - Use `custom-[color-name]` for non-standard colors.
		 *
		 * Add the line below to disable the custom color picker in the editor.
		 * add_theme_support( 'disable-custom-colors' );
		 */
		add_theme_support(
			'editor-color-palette',
			$editor_color_palette
		);

		/*
		 * Add support custom font sizes.
		 *
		 * Add the line below to disable the custom color picker in the editor.
		 * add_theme_support( 'disable-custom-font-sizes' );
		 */
		add_theme_support(
			'editor-font-sizes',
			[
				[
					'name'      => __( 'Small', 'wp-rig' ),
					'shortName' => __( 'S', 'wp-rig' ),
					'size'      => 16,
					'slug'      => 'small',
				],
				[
					'name'      => __( 'Medium', 'wp-rig' ),
					'shortName' => __( 'M', 'wp-rig' ),
					'size'      => 25,
					'slug'      => 'medium',
				],
				[
					'name'      => __( 'Large', 'wp-rig' ),
					'shortName' => __( 'L', 'wp-rig' ),
					'size'      => 31,
					'slug'      => 'large',
				],
				[
					'name'      => __( 'Larger', 'wp-rig' ),
					'shortName' => __( 'XL', 'wp-rig' ),
					'size'      => 39,
					'slug'      => 'larger',
				],
			]
		);
	}

	/**
	 * Adds block style variations for certain blocks.
	 */
	public function action_add_block_style_variations() {
		$style_variations = [
			'core/button' => [
				[
					'name'  => 'primary',
					'label' => _x( 'Primary', 'button style', 'wp-rig' ),
				],
				[
					'name'  => 'secondary',
					'label' => _x( 'Secondary', 'button style', 'wp-rig' ),
				],
			],
			'atomic-blocks/ab-button' => [
				[
					'name'  => 'primary',
					'label' => _x( 'Primary', 'button style', 'wp-rig' ),
				],
				[
					'name'  => 'secondary',
					'label' => _x( 'Secondary', 'button style', 'wp-rig' ),
				],
			],
		];

		$script = '';
		foreach ( $style_variations as $block_type => $variations ) {
			foreach ( $variations as $variation ) {
				$variation = wp_json_encode( $variation );
				$script   .= "wp.blocks.registerBlockStyle( '{$block_type}', {$variation} );";
			}
		}

		wp_add_inline_script( 'wp-blocks', $script, 'after' );
	}
}
