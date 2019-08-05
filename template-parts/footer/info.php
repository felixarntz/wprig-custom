<?php
/**
 * Template part for displaying the footer info
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

?>

<div class="site-info">
	<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'wp-rig' ) ); ?>">
		<?php
		/* translators: %s: CMS name, i.e. WordPress. */
		printf( esc_html__( 'Proudly powered by %s', 'wp-rig' ), 'WordPress' );
		?>
	</a>
	<span class="sep"> | </span>
	<?php
	/* translators: Theme name. */
	printf( esc_html__( 'Theme: %s by the contributors.', 'wp-rig' ), '<a href="' . esc_url( 'https://github.com/wprig/wprig/' ) . '">WP Rig</a>' );

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
