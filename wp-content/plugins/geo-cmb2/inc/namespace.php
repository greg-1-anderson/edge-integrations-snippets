<?php
/**
 * Geo CMB2 Tests
 *
 * @package Pantheon/EdgeIntegrations
 */

namespace Pantheon\EI\WP\Geo\CMB2;

use Pantheon\EI\WP\Geo;

/**
 * Kick off the plugin.
 */
function bootstrap() {
	add_action( 'admin_init', __NAMESPACE__ . '\\check_dependencies' );
	add_action( 'cmb2_admin_init', __NAMESPACE__ . '\\register_cmb2_metaboxes' );
	add_filter( 'the_content', __NAMESPACE__ . '\\render_the_geo_content' );
}

/**
 * Check if our dependencies exist.
 */
function check_dependencies() {
	if (
		is_admin() &&
		current_user_can( 'activate_plugins' ) &&
		(
			! is_plugin_active( 'pantheon-wordpress-edge-integrations/pantheon-wordpress-edge-integrations.php' ) ||
			! is_plugin_active( 'cmb2/init.php' )
		)
	) {
		add_action( 'admin_notices', __NAMESPACE__ . '\\plugin_notice' );
		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}

/**
 * Display a notice if our dependencies are not installed.
 */
function plugin_notice() {
	?>
	<div class="error">
		<p>
			<?php
			printf(
				__( 'The %1$s plugin requires %2$s and %3$s to be installed and active.', 'geo-cmb2' ),
				'<strong>' . esc_html__( 'Geo CMB2', 'geo-cmb2' ) . '</strong>',
				'<strong>' . esc_html__( 'Pantheon WordPress Edge Integrations', 'geo-cmb2' ) . '</strong>',
				'<strong>' . esc_html__( 'CMB2', 'geo-cmb2' ) . '</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Register the CMB2 fields.
 */
function register_cmb2_metaboxes() {
	$cmb = new_cmb2_box( array(
		'id'            => 'geo_cmb2_metabox',
		'title'         => __( 'CMB2 Geo', 'geo-cmb2' ),
		'object_types'  => array( 'page', ),
		'context'       => 'normal',
		'priority'      => 'low',
		'closed'     	=> true,
	) );

	$cmb->add_field( array(
		'name'       => __( 'US Content', 'geo-cmb2' ),
		'desc'       => __( 'Content to show to US-based visitors.', 'geo-cmb2' ),
		'id'         => 'us_cmb2_content',
		'type'       => 'text',
	) );

	$cmb->add_field( array(
		'name'       => __( 'CA Content', 'geo-cmb2' ),
		'desc'       => __( 'Content to show to CA-based visitors.', 'geo-cmb2' ),
		'id'         => 'ca_cmb2_content',
		'type'       => 'text',
	) );

	$cmb->add_field( array(
		'name'       => __( 'FR Content', 'geo-cmb2' ),
		'desc'       => __( 'Content to show to FR-based visitors.', 'geo-cmb2' ),
		'id'         => 'fr_cmb2_content',
		'type'       => 'text',
	) );

	$cmb->add_field( array(
		'name'       => __( 'Default Content', 'geo-cmb2' ),
		'id'         => 'default_cmb2_content',
		'type'       => 'text',
	) );
}

/**
 * Filter the_content to display Geo content.
 *
 * @param string $content The post content.
 *
 * @return string The filtered post content.
 */
function render_the_geo_content( string $content ) : string {
	// Get the geo content and the geo country.
	$geo = strtolower( Geo\get_geo( 'country' ) );
	$geo_content = get_post_meta( get_the_ID(), $geo . '_cmb2_content', true );
	$default_content = get_post_meta( get_the_ID(), 'default_cmb2_content', true );

	$content .= '<!-- Geo CMB2 Content -->';
	if ( ! $geo_content ) {
		$content .= '<p>' . esc_html( $default_content ) . '</p>';
	}

	if ( isset( $geo_content ) ) {
		$content .= '<p>' . esc_html( $geo_content ) . '</p>';
	}
	$content .= '<!-- End Geo CMB2 Content -->';

	return $content;
}
