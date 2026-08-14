<?php
/**
 * Plugin Name:       Green World Core
 * Plugin URI:        https://greenworldheath.com/
 * Description:       Business logic for Green World Health Solutions: automatic WhatsApp notifications (Meta Cloud API), scan bookings, the customer health dashboard, and the distributor dashboard with admin activation. The distributor points ledger arrives in a later phase. Keeps this data independent of the active theme.
 * Version:           0.3.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Green World Health Solutions
 * Text Domain:       greenworld-core
 */

defined( 'ABSPATH' ) || exit;

define( 'GWC_VERSION', '0.3.0' );
define( 'GWC_FILE', __FILE__ );
define( 'GWC_DIR', plugin_dir_path( __FILE__ ) );
define( 'GWC_URL', plugin_dir_url( __FILE__ ) );

require_once GWC_DIR . 'includes/class-gwc-settings.php';
require_once GWC_DIR . 'includes/class-gwc-whatsapp.php';
require_once GWC_DIR . 'includes/class-gwc-scan.php';
require_once GWC_DIR . 'includes/class-gwc-consultation.php';
require_once GWC_DIR . 'includes/class-gwc-records.php';
require_once GWC_DIR . 'includes/class-gwc-account.php';
require_once GWC_DIR . 'includes/class-gwc-distributor.php';
require_once GWC_DIR . 'includes/class-gwc-plugin.php';

register_activation_hook( __FILE__, array( 'GWC_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'GWC_Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'GWC_Plugin', 'boot' ) );
