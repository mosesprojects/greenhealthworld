<?php
declare( strict_types=1 );

namespace GreenWorld\Performance;

use GreenWorld\Core\Bootable;

defined( 'ABSPATH' ) || exit;

/**
 * Core Web Vitals optimizations: preload, resource hints, lazy media, emoji
 * removal, and self-hosted-font preconnect. Complements a caching plugin —
 * does not duplicate page caching.
 */
final class Optimizer implements Bootable {

	public function boot(): void {
		add_action( 'wp_head', [ $this, 'preload' ], 1 );
		add_filter( 'wp_resource_hints', [ $this, 'resource_hints' ], 10, 2 );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		add_filter( 'wp_lazy_loading_enabled', '__return_true' );
		add_action( 'init', [ $this, 'trim_head' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'trim_block_styles' ], 100 );
		add_filter( 'get_custom_logo', [ $this, 'lighten_logo' ], 20 );
	}

	/**
	 * Remove legacy head bloat (RSD, WLW manifest, shortlink, adjacent post
	 * links) that adds requests and leaks endpoints without SEO value.
	 */
	public function trim_head(): void {
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
	}

	public function preload(): void {
		printf( '<link rel="preload" href="%s" as="style" />' . "\n", esc_url( GREENWORLD_URI . 'assets/css/main.css' ) );
		if ( is_front_page() ) {
			$hero = (string) get_theme_mod( 'gw_hero_image', GREENWORLD_URI . 'assets/img/hero.jpg' );
			if ( $hero ) {
				printf( '<link rel="preload" href="%s" as="image" fetchpriority="high" />' . "\n", esc_url( $hero ) );
			}
		}
	}

	/**
	 * Preconnect to Google Maps only on the contact page, where the map iframe
	 * loads. The theme otherwise uses a system font stack, so there are no font
	 * origins worth hinting.
	 *
	 * @param array<int,string> $hints
	 * @return array<int,string>
	 */
	public function resource_hints( array $hints, string $relation ): array {
		if ( 'preconnect' === $relation && ( is_page( 'contact-us' ) || is_page( 'contact' ) ) ) {
			$hints[] = 'https://www.google.com';
			$hints[] = 'https://maps.gstatic.com';
		}
		return $hints;
	}

	public function trim_block_styles(): void {
		if ( is_admin() ) {
			return;
		}
		$post       = get_post();
		$has_blocks = ( is_object( $post ) && isset( $post->post_content ) ) ? has_blocks( (string) $post->post_content ) : false;
		if ( false === $has_blocks ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
		}
	}

	/**
	 * Keep the small header logo from being fetched at full size and from
	 * competing with the hero image for fetch priority.
	 */
	public function lighten_logo( string $html ): string {
		$html = str_replace( ' fetchpriority="high"', '', $html );
		$html = (string) preg_replace( '/ sizes="[^"]*"/', ' sizes="(max-width: 640px) 120px, 160px"', $html );
		return $html;
	}
}
