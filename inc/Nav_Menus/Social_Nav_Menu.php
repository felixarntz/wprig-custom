<?php
/**
 * WP_Rig\WP_Rig\Nav_Menus\Social_Nav_Menu class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Nav_Menus;

use function WP_Rig\WP_Rig\wp_rig;
use WP_Post;
use function add_filter;
use function add_action;
use function get_theme_mod;

/**
 * Class for managing the social navigation menu.
 */
class Social_Nav_Menu {

	const SLUG = 'social';

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_filter( 'walker_nav_menu_start_el', [ $this, 'filter_social_nav_menu_icons' ], 10, 4 );
	}

	/**
	 * Adjusts the social navigation menu to display SVG icons.
	 *
	 * @param string  $item_output The menu item output.
	 * @param WP_Post $item        Menu item object.
	 * @param int     $depth       Depth of the menu.
	 * @param object  $args        An object of wp_nav_menu() arguments.
	 * @return string Filtered $item_output.
	 */
	public function filter_social_nav_menu_icons( string $item_output, WP_Post $item, int $depth, $args ) {
		// Only for our social menu location.
		if ( empty( $args->theme_location ) || static::SLUG !== $args->theme_location ) {
			return $item_output;
		}

		// Find social icon by matching the link URL.
		$social_icons = $this->get_available_social_icons();
		foreach ( $social_icons as $attr => $value ) {
			if ( false !== strpos( $item_output, $attr ) ) {
				return str_replace( $args->link_after, $args->link_after . wp_rig()->get_svg_icon( $value ), $item_output );
			}
		}

		// Fall back to a simple external link icon.
		return str_replace( $args->link_after, $args->link_after . wp_rig()->get_svg_icon( 'chain' ), $item_output );
	}

	/**
	 * Gets the available social icons for the menu.
	 *
	 * @return array Associative array of $link_prefix => $social_icon pairs.
	 */
	protected function get_available_social_icons() {
		return [
			'behance.net'     => 'behance',
			'codepen.io'      => 'codepen',
			'deviantart.com'  => 'deviantart',
			'digg.com'        => 'digg',
			'docker.com'      => 'dockerhub',
			'dribbble.com'    => 'dribbble',
			'dropbox.com'     => 'dropbox',
			'mailto:'         => 'email',
			'facebook.com'    => 'facebook',
			'flickr.com'      => 'flickr',
			'foursquare.com'  => 'foursquare',
			'github.com'      => 'github',
			'plus.google.com' => 'google-plus',
			'instagram.com'   => 'instagram',
			'linkedin.com'    => 'linkedin',
			'medium.com'      => 'medium',
			'pscp.tv'         => 'periscope',
			'tel:'            => 'phone',
			'pinterest.com'   => 'pinterest',
			'getpocket.com'   => 'pocket',
			'reddit.com'      => 'reddit',
			'skype.com'       => 'skype',
			'skype:'          => 'skype',
			'slideshare.net'  => 'slideshare',
			'snapchat.com'    => 'snapchat',
			'soundcloud.com'  => 'soundcloud',
			'spotify.com'     => 'spotify',
			'stumbleupon.com' => 'stumbleupon',
			'tumblr.com'      => 'tumblr',
			'twitch.tv'       => 'twitch',
			'twitter.com'     => 'twitter',
			'vimeo.com'       => 'vimeo',
			'vine.co'         => 'vine',
			'vk.com'          => 'vk',
			'wordpress.org'   => 'wordpress',
			'wordpress.com'   => 'wordpress',
			'yelp.com'        => 'yelp',
			'youtube.com'     => 'youtube',
		];
	}
}
