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

/**
 * Register scripts and styles.
 *
 * @param string $hook_suffix The current admin page.
 */
function register_script( $hook_suffix ) {
	if ( 'post.php' !== $hook_suffix ) {
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
		'name'       => __( 'US content', 'geo-cmb2' ),
		'desc'       => __( 'Content to show to US-based visitors.', 'geo-cmb2' ),
		'id'         => 'us_cmb2_content',
		'type'       => 'textarea',
		'attributes' => [
			'data-conditional-id'     => 'country_text_select',
			'data-conditional-value'  => 'us',
		],
	] );

	$cmb->add_group_field( $group_repeat_test, [
		'name'       => __( 'CA content', 'geo-cmb2' ),
		'desc'       => __( 'Content to show to CA-based visitors.', 'geo-cmb2' ),
		'id'         => 'ca_cmb2_content',
		'type'       => 'textarea',
		'attributes' => [
			'data-conditional-id'     => 'country_text_select',
			'data-conditional-value'  => 'ca',
		],
	] );

	$cmb->add_group_field( $group_repeat_test, [
		'name'       => __( 'FR content', 'geo-cmb2' ),
		'desc'       => __( 'Content to show to FR-based visitors.', 'geo-cmb2' ),
		'id'         => 'fr_cmb2_content',
		'type'       => 'textarea',
		'attributes' => [
			'data-conditional-id'     => 'country_text_select',
			'data-conditional-value'  => 'fr',
		],
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
		$post_meta = get_post_meta( get_the_ID() );
		if ( isset( $post_meta['geo_cmb2_section'][0] ) ) {
			$translation_list = maybe_unserialize( $post_meta['geo_cmb2_section'][0] );
			foreach ( $translation_list as $translation ) {
				if ( isset( $translation['country_text_select'] ) ) {
					if ( $translation['country_text_select'] === $geo ) {
						$content .= wp_kses_post( $translation[ $geo . '_cmb2_content' ] );
					} else {
						$content .= wp_kses_post( $default_content );
					}
				}
			}
		}
	} elseif ( isset( $default_content ) ) {
		$content .= wp_kses_post( $default_content );
	}
	$content .= '<!-- End Geo CMB2 Content -->';

	return $content;
}
