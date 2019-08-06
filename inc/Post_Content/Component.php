<?php
/**
 * WP_Rig\WP_Rig\Post_Content\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Post_Content;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use WP_Post;
use function apply_filters;
use function get_post;

/**
 * Class for managing which data is shown for a post.
 *
 * Exposes template tags:
 * * `wp_rig()->showing_post_header( $post = null )`
 * * `wp_rig()->showing_post_footer( $post = null )`
 * * `wp_rig()->showing_post_meta( string $field, $post = null )`
 * * `wp_rig()->showing_post_taxonomy_terms( string $taxonomy, $post = null )`
 */
class Component implements Component_Interface, Templating_Component_Interface {

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'post_content';
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
			'showing_post_header'         => [ $this, 'showing_post_header' ],
			'showing_post_footer'         => [ $this, 'showing_post_footer' ],
			'showing_post_meta'           => [ $this, 'showing_post_meta' ],
			'showing_post_taxonomy_terms' => [ $this, 'showing_post_taxonomy_terms' ],
		];
	}

	/**
	 * Checks whether the post header should be displayed.
	 *
	 * @param mixed $post Optional. WordPress post object or ID. Default is the current post.
	 * @return bool True if the header should be displayed, false otherwise.
	 */
	public function showing_post_header( $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return false;
		}

		/**
		 * Filters whether the post header should be displayed.
		 *
		 * @param bool    $show Whether to display the header.
		 * @param WP_Post $post WordPress post object.
		 */
		return (bool) apply_filters( 'wp_rig_showing_post_header', true, $post );
	}

	/**
	 * Checks whether the post footer should be displayed.
	 *
	 * @param mixed $post Optional. WordPress post object or ID. Default is the current post.
	 * @return bool True if the footer should be displayed, false otherwise.
	 */
	public function showing_post_footer( $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return false;
		}

		/**
		 * Filters whether the post footer should be displayed.
		 *
		 * @param bool    $show Whether to display the footer.
		 * @param WP_Post $post WordPress post object.
		 */
		return (bool) apply_filters( 'wp_rig_showing_post_footer', true, $post );
	}

	/**
	 * Checks whether a specific post meta field should be displayed.
	 *
	 * @param string $field Name of the field, e.g. 'date', or 'author'.
	 * @param mixed  $post  Optional. WordPress post object or ID. Default is the current post.
	 * @return bool True if the data for the field should be displayed, false otherwise.
	 */
	public function showing_post_meta( string $field, $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return false;
		}

		/**
		 * Filters whether a specific post meta field should be displayed.
		 *
		 * @param bool    $show  Whether to display the data for the field.
		 * @param string  $field Name of the field, e.g. 'date', or 'author'.
		 * @param WP_Post $post  WordPress post object.
		 */
		return (bool) apply_filters( 'wp_rig_showing_post_meta', true, $field, $post );
	}

	/**
	 * Checks whether terms for a specific post taxonomy should be displayed.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @param mixed  $post     Optional. WordPress post object or ID. Default is the current post.
	 * @return bool True if the terms for the taxonomy should be displayed, false otherwise.
	 */
	public function showing_post_taxonomy_terms( string $taxonomy, $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return false;
		}

		/**
		 * Filters whether terms for a specific post taxonomy should be displayed.
		 *
		 * @param bool    $show     Whether to display the terms for the taxonomy.
		 * @param string  $taxonomy Taxonomy slug.
		 * @param WP_Post $post     WordPress post object.
		 */
		return (bool) apply_filters( 'wp_rig_showing_post_taxonomy_terms', true, $taxonomy, $post );
	}
}
