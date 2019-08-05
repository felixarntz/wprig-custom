<?php
/**
 * WP_Rig\WP_Rig\Nav_Menus\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Nav_Menus;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use WP_Post;
use function add_action;
use function add_filter;
use function register_nav_menus;
use function esc_html__;
use function has_nav_menu;
use function wp_nav_menu;

/**
 * Class for managing navigation menus.
 *
 * Exposes template tags:
 * * `wp_rig()->is_primary_nav_menu_active()`
 * * `wp_rig()->display_primary_nav_menu( array $args = [] )`
 * * `wp_rig()->is_footer_nav_menu_active()`
 * * `wp_rig()->display_footer_nav_menu( array $args = [] )`
 * * `wp_rig()->is_social_nav_menu_active()`
 * * `wp_rig()->display_social_nav_menu( array $args = [] )`
 */
class Component implements Component_Interface, Templating_Component_Interface {

	const PRIMARY_NAV_MENU_SLUG = 'primary';
	const FOOTER_NAV_MENU_SLUG = 'footer';

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'nav_menus';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_action( 'after_setup_theme', [ $this, 'action_register_nav_menus' ] );
		add_filter( 'walker_nav_menu_start_el', [ $this, 'filter_primary_nav_menu_dropdown_symbol' ], 10, 4 );

		$social_nav_menu = new Social_Nav_Menu();
		$social_nav_menu->initialize();
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
			'is_primary_nav_menu_active' => [ $this, 'is_primary_nav_menu_active' ],
			'display_primary_nav_menu'   => [ $this, 'display_primary_nav_menu' ],
			'is_footer_nav_menu_active' => [ $this, 'is_footer_nav_menu_active' ],
			'display_footer_nav_menu'   => [ $this, 'display_footer_nav_menu' ],
			'is_social_nav_menu_active'  => [ $this, 'is_social_nav_menu_active' ],
			'display_social_nav_menu'    => [ $this, 'display_social_nav_menu' ],
		];
	}

	/**
	 * Registers the navigation menus.
	 */
	public function action_register_nav_menus() {
		register_nav_menus(
			[
				static::PRIMARY_NAV_MENU_SLUG => esc_html__( 'Primary', 'wp-rig' ),
				static::FOOTER_NAV_MENU_SLUG  => esc_html__( 'Footer', 'wp-rig' ),
				Social_Nav_Menu::SLUG         => esc_html__( 'Social', 'wp-rig' ),
			]
		);
	}

	/**
	 * Adds a dropdown symbol to nav menu items with children.
	 *
	 * Adds the dropdown markup after the menu link element,
	 * before the submenu.
	 *
	 * Javascript converts the symbol to a toggle button.
	 *
	 * @TODO:
	 * - This doesn't work for the page menu because it
	 *   doesn't have a similar filter. So the dropdown symbol
	 *   is only being added for page menus if JS is enabled.
	 *   Create a ticket to add to core?
	 *
	 * @param string  $item_output The menu item's starting HTML output.
	 * @param WP_Post $item        Menu item data object.
	 * @param int     $depth       Depth of menu item. Used for padding.
	 * @param object  $args        An object of wp_nav_menu() arguments.
	 * @return string Modified nav menu HTML.
	 */
	public function filter_primary_nav_menu_dropdown_symbol( string $item_output, WP_Post $item, int $depth, $args ) : string {

		// Only for our primary menu location.
		if ( empty( $args->theme_location ) || static::PRIMARY_NAV_MENU_SLUG !== $args->theme_location ) {
			return $item_output;
		}

		// Add the dropdown for items that have children.
		if ( ! empty( $item->classes ) && in_array( 'menu-item-has-children', $item->classes ) ) {
			return $item_output . '<span class="dropdown"><i class="dropdown-symbol"></i></span>';
		}

		return $item_output;
	}

	/**
	 * Checks whether the primary navigation menu is active.
	 *
	 * @return bool True if the primary navigation menu is active, false otherwise.
	 */
	public function is_primary_nav_menu_active() : bool {
		return (bool) has_nav_menu( static::PRIMARY_NAV_MENU_SLUG );
	}

	/**
	 * Displays the primary navigation menu.
	 *
	 * @param array $args Optional. Array of arguments. See `wp_nav_menu()` documentation for a list of supported
	 *                    arguments.
	 */
	public function display_primary_nav_menu( array $args = [] ) {
		if ( ! isset( $args['container'] ) ) {
			$args['container'] = 'ul';
		}

		$args['theme_location'] = static::PRIMARY_NAV_MENU_SLUG;

		wp_nav_menu( $args );
	}

	/**
	 * Checks whether the footer navigation menu is active.
	 *
	 * @return bool True if the footer navigation menu is active, false otherwise.
	 */
	public function is_footer_nav_menu_active() : bool {
		return (bool) has_nav_menu( static::FOOTER_NAV_MENU_SLUG );
	}

	/**
	 * Displays the footer navigation menu.
	 *
	 * @param array $args Optional. Array of arguments. See `wp_nav_menu()` documentation for a list of supported
	 *                    arguments.
	 */
	public function display_footer_nav_menu( array $args = [] ) {
		if ( ! isset( $args['container'] ) ) {
			$args['container'] = false;
		}
		if ( ! isset( $args['menu_class'] ) ) {
			$args['menu_class'] = 'menu inline-menu';
		}

		$args['theme_location'] = static::FOOTER_NAV_MENU_SLUG;

		wp_nav_menu( $args );
	}

	/**
	 * Checks whether the social navigation menu is active.
	 *
	 * @return bool True if the social navigation menu is active, false otherwise.
	 */
	public function is_social_nav_menu_active() : bool {
		return (bool) has_nav_menu( Social_Nav_Menu::SLUG );
	}

	/**
	 * Displays the social navigation menu.
	 *
	 * @param array $args Optional. Array of arguments. See `wp_nav_menu()` documentation for a list of supported
	 *                    arguments.
	 */
	public function display_social_nav_menu( array $args = [] ) {
		if ( ! isset( $args['container'] ) ) {
			$args['container'] = false;
		}
		if ( ! isset( $args['menu_class'] ) ) {
			$args['menu_class'] = 'menu social-menu';
		}
		if ( ! isset( $args['link_before'] ) && ! isset( $args['link_after'] ) ) {
			$args['link_before'] = '<span class="screen-reader-text">';
			$args['link_after']  = '</span>';
		}

		$args['theme_location'] = Social_Nav_Menu::SLUG;
		$args['depth']          = 1;

		wp_nav_menu( $args );
	}
}
