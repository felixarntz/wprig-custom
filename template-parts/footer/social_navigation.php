<?php
/**
 * Template part for displaying the footer social navigation
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

if ( ! wp_rig()->is_social_nav_menu_active() ) {
	return;
}

?>
<nav id="social-navigation" class="social-navigation" aria-label="<?php esc_attr_e( 'Social Menu', 'wp-rig' ); ?>">
	<?php wp_rig()->display_social_nav_menu( [ 'menu_id' => 'social-menu' ] ); ?>
</nav>
