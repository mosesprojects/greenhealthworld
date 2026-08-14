<?php
/**
 * Bootstrap: wires the modules together and handles (de)activation.
 *
 * @package GreenWorldCore
 */

defined( 'ABSPATH' ) || exit;

final class GWC_Plugin {

	public static function boot(): void {
		GWC_Settings::instance()->boot();
		GWC_Scan::instance()->boot();
		GWC_Consultation::instance()->boot();
	}

	public static function activate(): void {
		GWC_Scan::instance()->register_cpt();
		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
