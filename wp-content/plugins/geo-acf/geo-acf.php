<?php
/**
 * Plugin Name: Geolocation ACF Tests
 * Description: Renders content with ACF based on the user's location.
 * Author: Chris Reynolds
 * Author URI: https://github.com/jazzsequence
 * Version: 1.0
 */

use Pantheon\EI\WP\Geo\ACF;

require_once __DIR__ . '/inc/namespace.php';

// Kick it off.
ACF\bootstrap();
