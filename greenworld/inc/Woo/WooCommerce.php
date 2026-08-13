<?php
declare( strict_types=1 );

namespace GreenWorld\Woo;

use GreenWorld\Core\Bootable;
use GreenWorld\Customizer\Customizer;

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce presentation layer for a premium health store: badges, wishlist,
 * trust signals, delivery estimate, sticky add-to-cart, ingredients / how-to-use
 * tabs and a responsible health disclaimer. No medical claims are invented.
 */
final class WooCommerce implements Bootable {

	public function boot(): void {
		add_action( 'after_setup_theme', [ $this, 'columns' ] );

		// Loop.
		add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'badges' ], 9 );
		add_action( 'woocommerce_before_shop_loop_item', [ $this, 'wishlist_button' ], 8 );

		// Single product.
		add_action( 'woocommerce_single_product_summary', [ $this, 'brand_eyebrow' ], 4 );
		add_action( 'woocommerce_single_product_summary', [ $this, 'whatsapp_button' ], 31 );
		add_action( 'woocommerce_single_product_summary', [ $this, 'trust_badges' ], 35 );
		add_action( 'woocommerce_single_product_summary', [ $this, 'delivery_estimate' ], 36 );
		add_action( 'woocommerce_after_single_product_summary', [ $this, 'product_disclaimer' ], 6 );
		add_action( 'wp_footer', [ $this, 'sticky_atc' ] );

		// Editable product info + tabs.
		add_action( 'woocommerce_product_options_general_product_data', [ $this, 'info_fields' ] );
		add_action( 'woocommerce_process_product_meta', [ $this, 'save_info' ] );
		add_filter( 'woocommerce_product_tabs', [ $this, 'tabs' ] );

		add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'cart_fragments' ] );

		// Clean shop chrome; the theme provides its own wrappers.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

		// One sale badge only: drop WooCommerce's default flash; badges() is the single indicator.
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	}

	public function columns(): void {
		add_filter( 'loop_shop_columns', static fn() => 4 );
		add_filter( 'loop_shop_per_page', static fn() => 24 );
	}

	/**
	 * @param array<string,string> $fragments
	 * @return array<string,string>
	 */
	public function cart_fragments( array $fragments ): array {
		$count = ( WC()->cart instanceof \WC_Cart ) ? WC()->cart->get_cart_contents_count() : 0;
		$fragments['span.gw-cart__count'] = '<span class="gw-cart__count">' . esc_html( (string) $count ) . '</span>';
		ob_start();
		woocommerce_mini_cart();
		$fragments['div.gw-minicart__body'] = '<div class="gw-minicart__body">' . (string) ob_get_clean() . '</div>';
		return $fragments;
	}

	public function badges(): void {
		global $product;
		if ( ! $product instanceof \WC_Product ) {
			return;
		}
		echo '<div class="gw-badges">';
		if ( $product->is_on_sale() ) {
			echo '<span class="gw-badge gw-badge--sale">' . esc_html__( 'Sale', 'greenworld' ) . '</span>';
		}
		$date = $product->get_date_created();
		if ( $date && ( time() - $date->getTimestamp() ) < 30 * DAY_IN_SECONDS ) {
			echo '<span class="gw-badge gw-badge--new">' . esc_html__( 'New', 'greenworld' ) . '</span>';
		}
		if ( ! $product->is_in_stock() ) {
			echo '<span class="gw-badge gw-badge--oos">' . esc_html__( 'Out of stock', 'greenworld' ) . '</span>';
		}
		echo '</div>';
	}

	public function wishlist_button(): void {
		global $product;
		if ( ! $product instanceof \WC_Product ) {
			return;
		}
		printf(
			'<button class="gw-wish" type="button" data-gw-wishlist="%d" aria-label="%s" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M12 21S4 14.5 4 8.8A4.2 4.2 0 0 1 12 6a4.2 4.2 0 0 1 8 2.8C20 14.5 12 21 12 21Z"/></svg></button>',
			(int) $product->get_id(),
			esc_attr__( 'Add to wishlist', 'greenworld' )
		);
	}

	/**
	 * Small brand kicker printed above the product title on the single-product
	 * page, mirroring the "Brand" line in the redesigned layout.
	 */
	public function brand_eyebrow(): void {
		global $product;
		if ( ! $product instanceof \WC_Product ) {
			return;
		}
		echo '<p class="gw-product-brand">' . esc_html__( 'Green World', 'greenworld' ) . '</p>';
	}

	/**
	 * "Order on WhatsApp" call-to-action inside the single-product summary,
	 * matching the loop button. Number + message template come from the
	 * Customizer, so nothing is hardcoded.
	 */
	public function whatsapp_button(): void {
		global $product;
		if ( ! $product instanceof \WC_Product ) {
			return;
		}
		$wa = (string) preg_replace( '/[^0-9]/', '', Customizer::val( 'gw_whatsapp' ) );
		if ( '' === $wa ) {
			return;
		}
		$tmpl = (string) Customizer::val( 'gw_wa_order_msg' );
		if ( '' === trim( $tmpl ) ) {
			$tmpl = 'Hi Green World Health Solutions, I would like to order: {product} ({url})';
		}
		$msg = str_replace(
			[ '{product}', '{url}' ],
			[ $product->get_name(), (string) get_permalink( $product->get_id() ) ],
			$tmpl
		);
		printf(
			'<a class="gw-wa-order gw-wa-order--single" href="%s" target="_blank" rel="nofollow noopener"><svg class="gw-wa-order__icon" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.33 4.97L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 1.82c2.16 0 4.19.84 5.72 2.37a8.06 8.06 0 0 1 2.37 5.72c0 4.46-3.63 8.09-8.1 8.09a8.1 8.1 0 0 1-4.12-1.13l-.3-.18-3.12.82.83-3.04-.19-.31a8.03 8.03 0 0 1-1.25-4.25c0-4.46 3.63-8.09 8.1-8.09Zm4.68 10.29c-.26-.13-1.51-.75-1.74-.83-.23-.09-.4-.13-.57.13-.17.26-.65.83-.8 1-.15.17-.29.19-.55.06-.26-.13-1.08-.4-2.06-1.27-.76-.68-1.28-1.52-1.43-1.78-.15-.26-.02-.4.11-.53.12-.12.26-.31.39-.46.13-.15.17-.26.26-.44.09-.17.04-.33-.02-.46-.06-.13-.57-1.38-.78-1.89-.21-.5-.42-.43-.57-.44l-.49-.01c-.17 0-.44.06-.68.33-.23.26-.89.87-.89 2.12 0 1.25.91 2.46 1.04 2.63.13.17 1.79 2.74 4.34 3.84.61.26 1.08.42 1.45.54.61.19 1.16.16 1.6.1.49-.07 1.51-.62 1.72-1.21.21-.6.21-1.11.15-1.21-.06-.11-.23-.17-.49-.3Z"/></svg><span>%s</span></a>',
			esc_url( 'https://wa.me/' . $wa . '?text=' . rawurlencode( $msg ) ),
			esc_html__( 'Order on WhatsApp', 'greenworld' )
		);
	}

	public function trust_badges(): void {
		$badges = [
			__( 'Quality health & wellness products', 'greenworld' ),
			__( 'Secure payment: M-Pesa, bank transfer or cash on delivery', 'greenworld' ),
			__( 'Discreet delivery across Kenya', 'greenworld' ),
		];
		echo '<ul class="gw-ptrust" aria-label="' . esc_attr__( 'Store guarantees', 'greenworld' ) . '">';
		foreach ( $badges as $b ) {
			echo '<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>' . esc_html( $b ) . '</li>';
		}
		echo '</ul>';
	}

	public function delivery_estimate(): void {
		$text = (string) get_theme_mod( 'gw_delivery_note', __( 'Reliable delivery across Kenya. Nairobi same/next day; countrywide in 1–4 business days.', 'greenworld' ) );
		if ( '' === trim( $text ) ) {
			return;
		}
		echo '<p class="gw-delivery"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7z"/><circle cx="7" cy="17" r="2"/><circle cx="18" cy="17" r="2"/></svg>' . esc_html( $text ) . '</p>';
	}

	public function product_disclaimer(): void {
		$text = Customizer::val( 'gw_default_disclaimer' );
		if ( '' === $text ) {
			return;
		}
		echo '<div class="gw-product-disclaimer"><p>' . esc_html( $text ) . '</p></div>';
	}

	public function info_fields(): void {
		echo '<div class="options_group">';
		woocommerce_wp_textarea_input( array(
			'id'          => '_gw_ingredients',
			'label'       => __( 'Ingredients / Composition', 'greenworld' ),
			'description' => __( 'Shown as a product tab. Enter only accurate, supplied information.', 'greenworld' ),
			'desc_tip'    => true,
		) );
		woocommerce_wp_textarea_input( array(
			'id'          => '_gw_howtouse',
			'label'       => __( 'How to Use', 'greenworld' ),
			'description' => __( 'Directions for use as provided on the product/label. Shown as a product tab.', 'greenworld' ),
			'desc_tip'    => true,
		) );
		echo '</div>';
	}

	public function save_info( $post_id ): void {
		$pid = (int) $post_id;
		if ( 0 === $pid ) {
			return;
		}
		foreach ( [ '_gw_ingredients', '_gw_howtouse' ] as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $pid, $key, sanitize_textarea_field( wp_unslash( (string) $_POST[ $key ] ) ) );
			} else {
				delete_post_meta( $pid, $key );
			}
		}
	}

	/**
	 * @param array<string,array<string,mixed>> $tabs
	 * @return array<string,array<string,mixed>>
	 */
	public function tabs( array $tabs ): array {
		global $product;
		if ( $product instanceof \WC_Product ) {
			$ingredients = trim( (string) get_post_meta( $product->get_id(), '_gw_ingredients', true ) );
			$howto       = trim( (string) get_post_meta( $product->get_id(), '_gw_howtouse', true ) );
			if ( $ingredients !== '' ) {
				$tabs['gw_ingredients'] = [
					'title'    => __( 'Ingredients', 'greenworld' ),
					'priority' => 22,
					'callback' => static function () use ( $ingredients ): void {
						echo '<h2>' . esc_html__( 'Ingredients / Composition', 'greenworld' ) . '</h2>';
						echo wp_kses_post( wpautop( $ingredients ) );
					},
				];
			}
			if ( $howto !== '' ) {
				$tabs['gw_howtouse'] = [
					'title'    => __( 'How to Use', 'greenworld' ),
					'priority' => 24,
					'callback' => static function () use ( $howto ): void {
						echo '<h2>' . esc_html__( 'How to Use', 'greenworld' ) . '</h2>';
						echo wp_kses_post( wpautop( $howto ) );
					},
				];
			}
		}
		$tabs['gw_delivery'] = [
			'title'    => __( 'Delivery', 'greenworld' ),
			'priority' => 30,
			'callback' => static function (): void {
				echo '<h2>' . esc_html__( 'Delivery Information', 'greenworld' ) . '</h2>';
				echo '<p>' . esc_html( (string) get_theme_mod( 'gw_delivery_note', __( 'Reliable delivery across Kenya. Nairobi same/next day; countrywide in 1–4 business days. Pay by M-Pesa, bank transfer or cash on delivery.', 'greenworld' ) ) ) . '</p>';
			},
		];
		return $tabs;
	}

	public function sticky_atc(): void {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		global $product;
		if ( ! $product instanceof \WC_Product ) {
			return;
		}
		printf(
			'<div class="gw-sticky-atc" role="region" aria-label="%1$s"><span class="gw-sticky-atc__name">%2$s</span><span class="gw-sticky-atc__price">%3$s</span><a class="gw-sticky-atc__btn button" href="#" data-add-to-cart="%4$d">%5$s</a></div>',
			esc_attr__( 'Add to cart', 'greenworld' ),
			esc_html( $product->get_name() ),
			wp_kses_post( $product->get_price_html() ),
			(int) $product->get_id(),
			esc_html__( 'Add to Cart', 'greenworld' )
		);
	}
}
