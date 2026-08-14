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
		GWC_Records::instance()->boot();
		GWC_Account::instance()->boot();

		// Flush rewrite rules once per version so new endpoints (e.g. the
		// "My Health" account tab) work after a plugin update without the
		// admin having to re-save permalinks.
		add_action( 'init', array( 'GWC_Plugin', 'maybe_flush' ), 99 );
	}

	public static function maybe_flush(): void {
		if ( get_option( 'gwc_rewrite_version' ) !== GWC_VERSION ) {
			flush_rewrite_rules();
			update_option( 'gwc_rewrite_version', GWC_VERSION );
		}
	}

	public static function activate(): void {
		GWC_Scan::instance()->register_cpt();
		GWC_Records::instance()->register_cpts();
		GWC_Account::instance()->add_endpoint();
		flush_rewrite_rules();
		update_option( 'gwc_rewrite_version', GWC_VERSION );
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
