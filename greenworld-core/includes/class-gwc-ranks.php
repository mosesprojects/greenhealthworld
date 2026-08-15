<?php
/**
 * Distributor rank engine for Green World Core.
 *
 * Builds a configurable rank ladder on top of the EXISTING points ledger
 * (GWC_Points / gw_batch) and referral structure (GWC_Distributor / _gw_sponsor).
 * No new ledger, no duplication:
 *
 *   - Lifetime points = the sum of a distributor's positive point batches (rank
 *     never drops when the current balance is spent or corrected down).
 *   - Direct referrals = how many distributors named this one as their sponsor.
 *   - Rank = the highest tier whose point + referral thresholds are both met.
 *
 * The ladder (names + thresholds) is fully admin-editable so it can be mapped
 * to Green World's real compensation plan. The engine surfaces a rank card on
 * the distributor dashboard (via the gwc_distributor_dashboard_active hook) and
 * a roster + ladder editor under Users -> Ranks.
 *
 * @package GreenWorldCore
 */

defined( 'ABSPATH' ) || exit;

final class GWC_Ranks {

	private static $instance = null;

	const OPTION = 'gwc_ranks';

	/* Existing keys we read (owned by GWC_Points / GWC_Distributor). */
	const BATCH_CPT     = 'gw_batch';
	const M_BATCH_USER  = '_gw_b_user';
	const M_BATCH_PTS   = '_gw_b_points';
	const M_SPONSOR     = '_gw_sponsor';

	const CACHE_TTL = 600; // 10 minutes.

	public static function instance(): GWC_Ranks {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function boot(): void {
		add_action( 'gwc_distributor_dashboard_active', array( $this, 'render_dashboard_card' ), 10, 1 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/* --------------------------------------------------------------- settings */

	public function defaults(): array {
		return array(
			'enabled'     => 1,
			'ladder_text' => "Distributor | 0 | 0\nBronze | 500 | 0\nSilver | 2000 | 0\nGold | 5000 | 3\nPlatinum | 15000 | 5\nDiamond | 40000 | 10",
		);
	}

	public function settings(): array {
		$saved = get_option( self::OPTION, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return array_merge( $this->defaults(), $saved );
	}

	public function register_settings(): void {
		register_setting( 'gwc_ranks_group', self::OPTION, array( $this, 'sanitize' ) );
	}

	public function sanitize( $input ): array {
		$d   = $this->defaults();
		$out = array();
		$out['enabled']     = empty( $input['enabled'] ) ? 0 : 1;
		$out['ladder_text'] = isset( $input['ladder_text'] ) ? sanitize_textarea_field( (string) $input['ladder_text'] ) : $d['ladder_text'];
		if ( '' === trim( $out['ladder_text'] ) ) {
			$out['ladder_text'] = $d['ladder_text'];
		}
		return $out;
	}

	public function is_enabled(): bool {
		$s = $this->settings();
		return ! empty( $s['enabled'] );
	}

	/**
	 * Parsed rank ladder, ascending by points, always starting from a 0/0 base.
	 *
	 * @return array<int,array{name:string,points:int,referrals:int}>
	 */
	public function ladder(): array {
		$s     = $this->settings();
		$lines = preg_split( '/\r\n|\r|\n/', (string) $s['ladder_text'] );
		$out   = array();
		foreach ( (array) $lines as $line ) {
			$line = trim( (string) $line );
			if ( '' === $line ) {
				continue;
			}
			$parts = array_map( 'trim', explode( '|', $line ) );
			$name  = isset( $parts[0] ) ? $parts[0] : '';
			if ( '' === $name ) {
				continue;
			}
			$out[] = array(
				'name'      => $name,
				'points'    => isset( $parts[1] ) ? max( 0, (int) $parts[1] ) : 0,
				'referrals' => isset( $parts[2] ) ? max( 0, (int) $parts[2] ) : 0,
			);
		}

		if ( empty( $out ) ) {
			$out[] = array(
				'name'      => __( 'Distributor', 'greenworld-core' ),
				'points'    => 0,
				'referrals' => 0,
			);
		}

		usort(
			$out,
			static function ( $a, $b ) {
				if ( $a['points'] === $b['points'] ) {
					return $a['referrals'] - $b['referrals'];
				}
				return $a['points'] - $b['points'];
			}
		);

		// Guarantee a base tier everyone qualifies for.
		if ( $out[0]['points'] > 0 || $out[0]['referrals'] > 0 ) {
			array_unshift(
				$out,
				array(
					'name'      => __( 'Distributor', 'greenworld-core' ),
					'points'    => 0,
					'referrals' => 0,
				)
			);
		}

		return $out;
	}

	/* ----------------------------------------------------------------- metrics */

	/** Lifetime points earned = sum of positive point batches (cached). */
	public static function lifetime_points( int $uid ): int {
		if ( $uid <= 0 ) {
			return 0;
		}
		$key    = 'gwc_rank_lp_' . $uid;
		$cached = get_transient( $key );
		if ( false !== $cached ) {
			return (int) $cached;
		}
		$ids = (array) get_posts(
			array(
				'post_type'   => self::BATCH_CPT,
				'post_status' => 'publish',
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_key'    => self::M_BATCH_USER, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'  => (string) $uid, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		$sum = 0;
		foreach ( $ids as $bid ) {
			$p = (int) get_post_meta( (int) $bid, self::M_BATCH_PTS, true );
			if ( $p > 0 ) {
				$sum += $p;
			}
		}
		set_transient( $key, $sum, self::CACHE_TTL );
		return $sum;
	}

	/** Current spendable balance (from the existing cached user meta). */
	public static function balance( int $uid ): int {
		if ( class_exists( 'GWC_Distributor' ) && is_callable( array( 'GWC_Distributor', 'points' ) ) ) {
			return (int) GWC_Distributor::points( $uid );
		}
		return (int) get_user_meta( $uid, '_gw_points_balance', true );
	}

	/** Direct referrals: distributors who named this one as sponsor (cached). */
	public static function direct_referrals( int $uid ): int {
		if ( $uid <= 0 ) {
			return 0;
		}
		$key    = 'gwc_rank_dr_' . $uid;
		$cached = get_transient( $key );
		if ( false !== $cached ) {
			return (int) $cached;
		}
		$user = get_userdata( $uid );
		if ( ! $user instanceof WP_User ) {
			return 0;
		}
		$code    = class_exists( 'GWC_Distributor' ) && is_callable( array( 'GWC_Distributor', 'ref_code' ) ) ? GWC_Distributor::ref_code( $uid ) : '';
		$needles = array_filter( array( $code, $user->user_login, $user->user_email ) );
		if ( empty( $needles ) ) {
			set_transient( $key, 0, self::CACHE_TTL );
			return 0;
		}
		$meta = array( 'relation' => 'OR' );
		foreach ( $needles as $needle ) {
			$meta[] = array(
				'key'     => self::M_SPONSOR,
				'value'   => $needle,
				'compare' => '=',
			);
		}
		$query = new WP_User_Query(
			array(
				'meta_query' => $meta, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'fields'     => 'ID',
				'number'     => 500,
			)
		);
		$count = (int) count( (array) $query->get_results() );
		set_transient( $key, $count, self::CACHE_TTL );
		return $count;
	}

	/**
	 * Full rank picture for a distributor.
	 *
	 * @return array<string,mixed>
	 */
	public function rank_for( int $uid ): array {
		$ladder = $this->ladder();
		$lp     = self::lifetime_points( $uid );
		$refs   = self::direct_referrals( $uid );

		$idx = 0;
		foreach ( $ladder as $i => $r ) {
			if ( $lp >= $r['points'] && $refs >= $r['referrals'] ) {
				$idx = $i;
			}
		}
		$current = $ladder[ $idx ];
		$next    = isset( $ladder[ $idx + 1 ] ) ? $ladder[ $idx + 1 ] : null;

		$points_to_next = ( null !== $next ) ? max( 0, (int) $next['points'] - $lp ) : 0;
		$refs_to_next   = ( null !== $next ) ? max( 0, (int) $next['referrals'] - $refs ) : 0;

		$pct = 100;
		if ( null !== $next ) {
			$span = (int) $next['points'] - (int) $current['points'];
			$done = $lp - (int) $current['points'];
			$pct  = $span > 0 ? (int) round( max( 0, min( 100, ( $done / $span ) * 100 ) ) ) : ( $points_to_next <= 0 ? 100 : 0 );
		}

		return array(
			'index'          => $idx,
			'name'           => $current['name'],
			'current'        => $current,
			'next'           => $next,
			'lifetime'       => $lp,
			'balance'        => self::balance( $uid ),
			'referrals'      => $refs,
			'points_to_next' => $points_to_next,
			'refs_to_next'   => $refs_to_next,
			'progress'       => $pct,
		);
	}

	/* ------------------------------------------------------- dashboard card */

	public function render_dashboard_card( $uid ): void {
		$uid = (int) $uid;
		if ( ! $this->is_enabled() || $uid <= 0 ) {
			return;
		}
		$info = $this->rank_for( $uid );

		echo '<div class="gw-card">';
		echo '<h3>' . esc_html__( 'Your rank', 'greenworld-core' ) . ' <span class="gw-badge gw-badge--active">' . esc_html( $info['name'] ) . '</span></h3>';

		if ( null !== $info['next'] ) {
			$pct = (int) $info['progress'];
			echo '<div style="background:#e7efe8;border-radius:999px;height:12px;overflow:hidden;margin:.5rem 0 .35rem;">';
			echo '<div style="width:' . esc_attr( (string) $pct ) . '%;height:100%;background:#1f7a3d;"></div>';
			echo '</div>';

			$bits = array();
			if ( (int) $info['points_to_next'] > 0 ) {
				$bits[] = sprintf(
					/* translators: 1: points, 2: next rank */
					__( '%1$s more points to reach %2$s', 'greenworld-core' ),
					number_format_i18n( (int) $info['points_to_next'] ),
					$info['next']['name']
				);
			}
			if ( (int) $info['refs_to_next'] > 0 ) {
				$bits[] = sprintf(
					/* translators: %d: number of referrals */
					_n( '%d more direct referral', '%d more direct referrals', (int) $info['refs_to_next'], 'greenworld-core' ),
					(int) $info['refs_to_next']
				);
			}
			if ( empty( $bits ) ) {
				$bits[] = sprintf(
					/* translators: %s: next rank name */
					__( 'You have met the requirements for %s.', 'greenworld-core' ),
					$info['next']['name']
				);
			}
			echo '<p class="gw-sub">' . esc_html( implode( ' - ', $bits ) ) . '</p>';
		} else {
			echo '<p class="gw-sub">' . esc_html__( 'You have reached our top rank. Congratulations!', 'greenworld-core' ) . '</p>';
		}

		echo '<dl class="gw-facts" style="margin-top:.5rem;">';
		echo '<dt>' . esc_html__( 'Lifetime points', 'greenworld-core' ) . '</dt><dd>' . esc_html( number_format_i18n( (int) $info['lifetime'] ) ) . '</dd>';
		echo '<dt>' . esc_html__( 'Current balance', 'greenworld-core' ) . '</dt><dd>' . esc_html( number_format_i18n( (int) $info['balance'] ) ) . '</dd>';
		echo '<dt>' . esc_html__( 'Direct referrals', 'greenworld-core' ) . '</dt><dd>' . esc_html( number_format_i18n( (int) $info['referrals'] ) ) . '</dd>';
		echo '</dl>';
		echo '<p class="gw-sub" style="margin-top:.6rem;">' . esc_html__( 'Rank is based on your lifetime points earned and the distributors you have introduced. Keep building to reach the next tier.', 'greenworld-core' ) . '</p>';
		echo '</div>';
	}

	/* ---------------------------------------------------------------- admin */

	public function admin_menu(): void {
		add_users_page(
			__( 'Distributor Ranks', 'greenworld-core' ),
			__( 'Ranks', 'greenworld-core' ),
			'edit_users',
			'gwc-ranks',
			array( $this, 'render_admin' )
		);
	}

	public function render_admin(): void {
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_die( esc_html__( 'You do not have permission to view distributor ranks.', 'greenworld-core' ) );
		}
		$s      = $this->settings();
		$ladder = $this->ladder();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Distributor Ranks', 'greenworld-core' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Ranks are calculated from your existing points ledger and referrals. Edit the ladder below to match your compensation plan; distributors see their rank and progress on their dashboard.', 'greenworld-core' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( 'gwc_ranks_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Rank engine', 'greenworld-core' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( self::OPTION ); ?>[enabled]" value="1" <?php checked( ! empty( $s['enabled'] ) ); ?> /> <?php esc_html_e( 'Show ranks on the distributor dashboard', 'greenworld-core' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><label for="gwc_ladder"><?php esc_html_e( 'Rank ladder', 'greenworld-core' ); ?></label></th>
						<td>
							<textarea id="gwc_ladder" name="<?php echo esc_attr( self::OPTION ); ?>[ladder_text]" rows="8" class="large-text code"><?php echo esc_textarea( (string) $s['ladder_text'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'One rank per line, lowest first: Name | lifetime points | direct referrals. The points and referrals columns are optional (default 0). Example: Gold | 5000 | 3', 'greenworld-core' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<h2><?php esc_html_e( 'Current ladder', 'greenworld-core' ); ?></h2>
			<table class="widefat striped" style="max-width:520px">
				<thead><tr>
					<th><?php esc_html_e( 'Rank', 'greenworld-core' ); ?></th>
					<th><?php esc_html_e( 'Lifetime points', 'greenworld-core' ); ?></th>
					<th><?php esc_html_e( 'Direct referrals', 'greenworld-core' ); ?></th>
				</tr></thead>
				<tbody>
				<?php foreach ( $ladder as $r ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $r['name'] ); ?></strong></td>
						<td><?php echo esc_html( number_format_i18n( (int) $r['points'] ) ); ?></td>
						<td><?php echo esc_html( number_format_i18n( (int) $r['referrals'] ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Distributor roster', 'greenworld-core' ); ?></h2>
			<?php $this->render_roster(); ?>
		</div>
		<?php
	}

	private function render_roster(): void {
		if ( ! class_exists( 'GWC_Distributor' ) ) {
			echo '<p>' . esc_html__( 'Distributor module unavailable.', 'greenworld-core' ) . '</p>';
			return;
		}
		$dists = get_users(
			array(
				'role'    => GWC_Distributor::ROLE,
				'orderby' => 'display_name',
				'order'   => 'ASC',
				'number'  => 200,
			)
		);
		if ( empty( $dists ) ) {
			echo '<p>' . esc_html__( 'No distributors yet.', 'greenworld-core' ) . '</p>';
			return;
		}

		echo '<table class="widefat striped" style="max-width:900px"><thead><tr>';
		echo '<th>' . esc_html__( 'Distributor', 'greenworld-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Status', 'greenworld-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Rank', 'greenworld-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Lifetime points', 'greenworld-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Balance', 'greenworld-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Direct referrals', 'greenworld-core' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $dists as $d ) {
			$id     = (int) $d->ID;
			$status = GWC_Distributor::status( $id );
			$info   = $this->rank_for( $id );
			echo '<tr>';
			echo '<td><strong>' . esc_html( $d->display_name ) . '</strong><br /><span style="color:#6a776e;font-size:.85em">' . esc_html( $d->user_email ) . '</span></td>';
			echo '<td>' . esc_html( ucfirst( $status ) ) . '</td>';
			echo '<td><strong>' . esc_html( $info['name'] ) . '</strong></td>';
			echo '<td>' . esc_html( number_format_i18n( (int) $info['lifetime'] ) ) . '</td>';
			echo '<td>' . esc_html( number_format_i18n( (int) $info['balance'] ) ) . '</td>';
			echo '<td>' . esc_html( number_format_i18n( (int) $info['referrals'] ) ) . '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
		echo '<p class="description">' . esc_html__( 'Showing up to 200 distributors. Figures refresh a few minutes after new batches or referrals.', 'greenworld-core' ) . '</p>';
	}
}
