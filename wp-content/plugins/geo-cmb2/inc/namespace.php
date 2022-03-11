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
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\register_script' );
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

function register_script( $hook ) {
	if ( 'post.php' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'geo-cmb2-styles', plugins_url( '/css/geo-cmb2.css', GEO_CMB2_FILE ), [], GEO_CMB2_VERSION );
	wp_enqueue_script( 'geo-cmb2-conditional-logic', plugins_url( '/js/conditional-logic.js', GEO_CMB2_FILE ), [], GEO_CMB2_VERSION, true );
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
	
	// Repeatable group
	$group_repeat_test = $cmb->add_field( array(
		'id'          => 'geo_cmb2_section',
		'type'        => 'group',
		'options'     => array(
			'group_title'   => __( 'Language', 'geo-cmb2' ) . ' {#}', // {#} gets replaced by row number
			'add_button'    => __( 'Add another Language', 'geo-cmb2' ),
			'remove_button' => __( 'Remove Language', 'geo-cmb2' ),
		),
	) );

	//* Title
	$cmb->add_group_field( $group_repeat_test, array(
		'name'             => 'Country Select',
		'desc'             => 'Select an option',
		'id'               => 'country_text_select',
		'type'             => 'select',
		'show_option_none' => true,
		'default'          => 'custom',
		'options'          => array(
			'us' => __( 'US content', 'geo-cmb2' ),
			'ca' => __( 'Canadian content', 'geo-cmb2' ),
			'fr' => __( 'French content', 'geo-cmb2' ),
		),
	) );

	$cmb->add_group_field( $group_repeat_test, array(
		'name'       => __( 'US content', 'geo-cmb2' ),
		'desc'       => __( 'Content to show to US-based visitors.', 'geo-cmb2' ),
		'id'         => 'us_cmb2_content',
		'type'       => 'text',
        'attributes' => array(
            'data-conditional-id'     => 'country_text_select',
            'data-conditional-value'  => 'us',
        ),
	) );

	$cmb->add_group_field( $group_repeat_test, array(
		'name'       => __( 'CA content', 'geo-cmb2' ),
		'desc'       => __( 'Content to show to CA-based visitors.', 'geo-cmb2' ),
		'id'         => 'ca_cmb2_content',
		'type'       => 'text',
        'attributes' => array(
            'data-conditional-id'     => 'country_text_select',
            'data-conditional-value'  => 'ca',
        ),
	) );

	$cmb->add_group_field( $group_repeat_test, array(
		'name'       => __( 'FR content', 'geo-cmb2' ),
		'desc'       => __( 'Content to show to FR-based visitors.', 'geo-cmb2' ),
		'id'         => 'fr_cmb2_content',
		'type'       => 'text',
        'attributes' => array(
            'data-conditional-id'     => 'country_text_select',
            'data-conditional-value'  => 'fr',
        ),
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
