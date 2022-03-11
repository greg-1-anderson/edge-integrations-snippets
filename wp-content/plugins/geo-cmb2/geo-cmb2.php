<?php
/**
 * Plugin Name: Geolocation CMB2 Tests
 * Description: Renders content with CMB2 based on the user's location.
 * Author: Pantheon
 * Author URI: https://github.com/pantheon-systems
 * Version: 1.0
 */

use Pantheon\EI\WP\Geo\CMB2;

require_once __DIR__ . '/inc/namespace.php';

// Kick it off.
CMB2\bootstrap();
