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
	define( 'GEO_CMB2_DIR', dirname( __DIR__, 1 ) );
	define( 'GEO_CMB2_FILE', GEO_CMB2_DIR . '/' . basename( dirname( __DIR__, 1 ) ) . '.php' );

	$plugin_data = get_file_data( GEO_CMB2_FILE, [ 'Version' => 'Version' ] );
	$plugin_version = $plugin_data['Version'];
	define( 'GEO_CMB2_VERSION', $plugin_version );

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
	$cmb = new_cmb2_box( [
		'id'            => 'geo_cmb2_metabox',
		'title'         => __( 'CMB2 Geo', 'geo-cmb2' ),
		'object_types'  => [ 'page' ],
		'context'       => 'normal',
		'priority'      => 'low',
		'closed'        => true,
	] );

	$cmb->add_field( [
		'name'       => __( 'Default content', 'geo-cmb2' ),
		'desc'       => __( 'Content to show to site visitors.', 'geo-cmb2' ),
		'id'         => 'default_cmb2_content',
		'type'       => 'textarea',
	] );

	// Declare our repeatable group.
	$group_repeat_test = $cmb->add_field( [
		'id'          => 'geo_cmb2_section',
		'type'        => 'group',
		'options'     => [
			'group_title'   => __( 'Translation', 'geo-cmb2' ) . ' {#}', // {#} gets replaced by row number
			'add_button'    => __( 'Add another Translation', 'geo-cmb2' ),
			'remove_button' => __( 'Remove Translation', 'geo-cmb2' ),
		],
	] );

	$cmb->add_group_field( $group_repeat_test, [
		'name'             => 'Country Select',
		'desc'             => 'Select an option',
		'id'               => 'country_text_select',
		'type'             => 'select',
		'show_option_none' => false,
		'default'          => 'custom',
		'options'          => [
			'none' => __( 'None', 'geo-cmb2' ),
			'us'   => __( 'US', 'geo-cmb2' ),
			'ca'   => __( 'Canada', 'geo-cmb2' ),
			'fr'   => __( 'France', 'geo-cmb2' ),
		],
	] );

	$cmb->add_group_field( $group_repeat_test, [
		'name'       => __( 'Translation content', 'geo-cmb2' ),
		'desc'       => __( 'Content to show to location-specific visitors.', 'geo-cmb2' ),
		'id'         => 'cmb2_content',
		'type'       => 'textarea',
	] );
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
	$default_content = get_post_meta( get_the_ID(), 'default_cmb2_content', true );

	$content .= '<!-- Geo CMB2 Content -->';
	if ( ! empty( $geo ) ) {
		$post_meta = get_post_meta( get_the_ID(), 'geo_cmb2_section', true );
		$countries = wp_list_pluck( $post_meta, 'country_text_select' );
		$field_key = 'cmb2_content';

		if ( in_array( $geo, $countries, true ) ) {
			$i = array_search( $geo, $countries, true );
			$geo_content = isset( $post_meta[ $i ][ $field_key ] ) ? $post_meta[ $i ][ $field_key ] : false;

			if ( ! empty( $geo_content ) ) {
				$content .= wp_kses_post( $geo_content );
			}
		}
	} elseif ( isset( $default_content ) ) {
		$content .= wp_kses_post( $default_content );
	}
	$content .= '<!-- End Geo CMB2 Content -->';

	return $content;
}
