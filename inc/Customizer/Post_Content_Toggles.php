<?php
/**
 * WP_Rig\WP_Rig\Customizer\Post_Content_Toggles class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Customizer;

use WP_Customize_Manager;
use WP_Post_Type;
use WP_Post;
use function add_filter;
use function add_action;
use function get_theme_mod;
use function get_post_type;
use function get_post_type_object;
use function get_post_types;
use function post_type_supports;
use function get_object_taxonomies;

/**
 * Class for managing Customizer support for toggling content displayed for a post.
 */
class Post_Content_Toggles {

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_filter( 'wp_rig_showing_post_meta', [ $this, 'filter_wp_rig_showing_post_meta' ], 10, 3 );
		add_filter( 'wp_rig_showing_post_taxonomy_terms', [ $this, 'filter_wp_rig_showing_post_taxonomy_terms' ], 10, 3 );
		add_action( 'customize_register', [ $this, 'action_customize_register' ] );
	}

	/**
	 * Filters whether a specific post meta field should be displayed.
	 *
	 * @param bool    $show  Whether to display the data for the field.
	 * @param string  $field Name of the field, e.g. 'date', or 'author'.
	 * @param WP_Post $post  WordPress post object.
	 * @return bool Filtered value of $show.
	 */
	public function filter_wp_rig_showing_post_meta( bool $show, string $field, WP_Post $post ) {
		$post_type = get_post_type( $post );
		if ( ! $post_type ) {
			return false;
		}

		$prefix = $this->get_prefix( $post_type );

		$result = get_theme_mod( $prefix . 'show_' . $field, 'DEFAULT' );

		if ( 'DEFAULT' === $result ) {
			$metadata = $this->get_metadata( get_post_type_object( $post_type ) );
			foreach ( $metadata as $metadata_field ) {
				if ( $metadata_field['setting'] === $prefix . 'show_' . $field && isset( $metadata_field['default'] ) ) {
					return (bool) $metadata_field['default'];
				}
			}
			return $show;
		}

		return (bool) $result;
	}

	/**
	 * Filters whether terms for a specific post taxonomy should be displayed.
	 *
	 * @param bool    $show     Whether to display the terms for the taxonomy.
	 * @param string  $taxonomy Taxonomy slug.
	 * @param WP_Post $post     WordPress post object.
	 * @return bool Filtered value of $show.
	 */
	public function filter_wp_rig_showing_post_taxonomy_terms( bool $show, string $taxonomy, WP_Post $post ) {
		$post_type = get_post_type( $post );
		if ( ! $post_type ) {
			return false;
		}

		$prefix = $this->get_prefix( $post_type );

		return (bool) get_theme_mod( $prefix . 'show_terms_' . $taxonomy, $show );
	}

	/**
	 * Adds Customizer settings and controls for toggling content.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register( WP_Customize_Manager $wp_customize ) {
		$public_post_types = get_post_types( array( 'public' => true ), 'objects' );

		$wp_customize->add_panel(
			'content_types',
			array(
				'title'    => __( 'Content Types', 'wp-rig' ),
				'priority' => 140,
			)
		);

		foreach ( $public_post_types as $post_type ) {
			$prefix = $this->get_prefix( $post_type->name );

			$wp_customize->add_section(
				$prefix . 'settings',
				array(
					'title' => $post_type->label,
					'panel' => 'content_types',
				)
			);

			$meta_partial_settings       = [];
			$taxonomies_partial_settings = [];

			$metadata = $this->get_metadata( $post_type );
			foreach ( $metadata as $field ) {
				$wp_customize->add_setting(
					$field['setting'],
					[
						'default'              => $field['default'],
						'transport'            => 'postMessage',
						'sanitize_callback'    => 'rest_sanitize_boolean',
						'sanitize_js_callback' => 'rest_sanitize_boolean',
					]
				);
				$wp_customize->add_control(
					$field['setting'],
					[
						'type'    => 'checkbox',
						'label'   => $field['title'],
						'section' => $prefix . 'settings',
					]
				);

				$meta_partial_settings[] = $field['setting'];
			}

			$taxonomies = $this->get_taxonomies( $post_type );
			foreach ( $taxonomies as $field ) {
				$wp_customize->add_setting(
					$field['setting'],
					[
						'default'              => $field['default'],
						'transport'            => 'postMessage',
						'sanitize_callback'    => 'rest_sanitize_boolean',
						'sanitize_js_callback' => 'rest_sanitize_boolean',
					]
				);
				$wp_customize->add_control(
					$field['setting'],
					[
						'type'    => 'checkbox',
						'label'   => $field['title'],
						'section' => $prefix . 'settings',
					]
				);

				$taxonomies_partial_settings[] = $field['setting'];
			}

			if ( isset( $wp_customize->selective_refresh ) ) {
				if ( ! empty( $meta_partial_settings ) ) {
					$wp_customize->selective_refresh->add_partial(
						$prefix . 'entry_meta',
						[
							'settings'            => $meta_partial_settings,
							'selector'            => '.type-' . $post_type->name . ' .entry-meta',
							'container_inclusive' => true,
							'type'                => 'post_instance', // Custom partial type, see customizer.js script.
							'render_callback'     => function( $partial, $context ) {
								if ( ! is_array( $context ) || empty( $context['post_id'] ) ) {
									return;
								}
								$this->print_content_template_part_for_post( 'entry_meta', (int) $context['post_id'] );
							},
						]
					);
				}

				if ( ! empty( $taxonomies_partial_settings ) ) {
					$wp_customize->selective_refresh->add_partial(
						$prefix . 'entry_taxonomies',
						[
							'settings'            => $taxonomies_partial_settings,
							'selector'            => '.type-' . $post_type->name . ' .entry-taxonomies',
							'container_inclusive' => true,
							'type'                => 'post_instance', // Custom partial type, see customizer.js script.
							'render_callback'     => function( $partial, $context ) {
								if ( ! is_array( $context ) || empty( $context['post_id'] ) ) {
									return;
								}
								$this->print_content_template_part_for_post( 'entry_taxonomies', (int) $context['post_id'] );
							},
						]
					);
				}
			}
		}
	}

	/**
	 * Prints a given content-specific template part for a given post.
	 *
	 * @param string $template_part Template part slug, relative to the 'template-parts/content/' directory.
	 * @param mixed  $post          Optional. WordPress post object or ID. Default is the current post.
	 */
	protected function print_content_template_part_for_post( string $template_part, $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return;
		}

		if ( $post !== $GLOBALS['post'] ) {
			$orig_post = $GLOBALS['post'];

			$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			setup_postdata( $post );
		}

		get_template_part( 'template-parts/content/' . $template_part, $post->post_type );

		if ( isset( $orig_post ) ) {
			$GLOBALS['post'] = $orig_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			setup_postdata( $orig_post );
		}
	}

	/**
	 * Gets meta fields that can be toggled in the Customizer.
	 *
	 * @param WP_Post_Type $post_type Post type object.
	 * @return array List of meta fields data.
	 */
	protected function get_metadata( WP_Post_Type $post_type ) {
		$prefix = $this->get_prefix( $post_type->name );

		$metadata = [
			[
				'setting' => $prefix . 'show_date',
				'title'   => __( 'Show Date?', 'wp-rig' ),
				'default' => 'post' === $post_type->name || $post_type->has_archive,
			],
		];

		if ( post_type_supports( $post_type->name, 'author' ) ) {
			$metadata[] = [
				'setting' => $prefix . 'show_author',
				'title'   => __( 'Show Author?', 'wp-rig' ),
				'default' => is_multi_author(),
			];
		}

		return $metadata;
	}

	/**
	 * Gets taxonomies that can be toggled in the Customizer.
	 *
	 * @param WP_Post_Type $post_type Post type object.
	 * @return array List of taxonomies data.
	 */
	protected function get_taxonomies( WP_Post_Type $post_type ) {
		$prefix = $this->get_prefix( $post_type->name );

		$public_taxonomies = wp_list_filter(
			get_object_taxonomies( $post_type->name, 'objects' ),
			array( 'public' => true )
		);

		$taxonomies = [];
		foreach ( $public_taxonomies as $taxonomy ) {
			$taxonomies[] = [
				'setting' => $prefix . 'show_terms_' . $taxonomy->name,
				/* translators: %s: the plural taxonomy label */
				'title'   => sprintf( _x( 'Show %s?', 'taxonomy terms', 'wp-rig' ), $taxonomy->label ),
				'default' => true,
			];
		}

		return $taxonomies;
	}

	/**
	 * Gets the prefix for post type specific data.
	 *
	 * @param string $post_type Post type name.
	 * @return string Prefix for data of that post type.
	 */
	protected function get_prefix( string $post_type ) {
		return "post_type_{$post_type}_";
	}
}
