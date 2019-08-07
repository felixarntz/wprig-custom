<?php
/**
 * Template part for displaying the footer info
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

?>

<div class="site-info">
	<?php
	wp_rig()->display_footer_info();

	if ( function_exists( 'the_privacy_policy_link' ) ) {
		the_privacy_policy_link( '<span class="sep"> | </span>' );
	}
	if ( wp_rig()->is_footer_nav_menu_active() ) {
		?>
		<span class="sep"> | </span>
		<?php
		wp_rig()->display_footer_nav_menu();
	}
	?>
</div><!-- .site-info -->
