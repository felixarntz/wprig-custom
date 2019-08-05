<?php
/**
 * Template part for displaying the footer widget areas
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

if ( ! wp_rig()->has_active_footer_widget_areas() ) {
	return;
}

$widget_area_count = wp_rig()->get_footer_widget_area_count();

?>
<aside id="footer-widget-areas" class="footer-widget-areas alignwide" aria-label="<?php esc_attr_e( 'Footer Widgets', 'wp-rig' ); ?>">
	<?php
	for ( $column = 1; $column <= $widget_area_count; $column++ ) {
		if ( ! wp_rig()->is_footer_widget_area_active( $column ) ) {
			continue;
		}
		?>
		<div id="<?php echo esc_attr( 'footer-widget-area-' . $column ); ?>" class="footer-widget-area">
			<?php wp_rig()->display_footer_widget_area( $column ); ?>
		</div>
		<?php
	}
	?>
</aside>
