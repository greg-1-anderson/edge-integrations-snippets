<?php
/**
 * Geo ACF Tests
 *
 * @package Pantheon/EdgeIntegrations
 */

namespace Pantheon\EI\WP\Geo\ACF;

use Pantheon\EI\WP\Geo;

/**
 * Kick off the plugin.
 */
function bootstrap() {
	add_action( 'admin_init', __NAMESPACE__ . '\\check_dependencies' );
	add_action( 'init', __NAMESPACE__ . '\\register_acf_fields' );
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
			! is_plugin_active( 'advanced-custom-fields/acf.php' )
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
				__( 'The %1$s plugin requires %2$s and %3$s to be installed and active.', 'geo-acf' ),
				'<strong>' . esc_html__( 'Geo ACF', 'geo-acf' ) . '</strong>',
				'<strong>' . esc_html__( 'Pantheon WordPress Edge Integrations', 'geo-acf' ) . '</strong>',
				'<strong>' . esc_html__( 'Advanced Custom Fields', 'geo-acf' ) . '</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Register the ACF fields.
 */
function register_acf_fields() {
	acf_add_local_field_group([
		'key' => 'group_621e5e5f8c64f',
		'title' => 'Geo',
		'fields' => [
			[
				'key' => 'field_621e5e72a4a3a',
				'label' => 'US Content',
				'name' => 'us_content',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			],
			[
				'key' => 'field_621e5e7fa4a3b',
				'label' => 'CA Content',
				'name' => 'ca_content',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			],
			[
				'key' => 'field_621e5e86a4a3c',
				'label' => 'FR Content',
				'name' => 'fr_content',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			],
			[
				'key' => 'field_621e5e8da4a3d',
				'label' => 'Default Content',
				'name' => 'default_content',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			],
		],
		'location' => [
			[
				[
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'post',
				],
			],
			[
				[
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
				],
			],
		],
		'menu_order' => 0,
		'position' => 'acf_after_title',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
		'show_in_rest' => 0,
	]);
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
	$geo_content = get_field( 'group_621e5e5f8c64f' );
	$geo = strtolower( Geo\get_geo( 'country' ) );
var_dump(get_fields(),$geo_content, $geo);//phpcs:ignore
	if ( ! $geo_content ) {
		return $content;
	}

	$content .= '<!-- Geo Content -->';
	if ( isset( $geo_content[ $geo . '_content' ] ) ) {
		$content .= $geo_content[ $geo . '_content' ];
	} else {
		$content .= $geo_content['default_content'];
	}
	$content .= '<!-- End Geo Content -->';

	return $content;
}
