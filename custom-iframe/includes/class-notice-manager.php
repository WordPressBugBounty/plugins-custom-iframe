<?php

namespace custif\includes;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notices Manager Class
 *
 * Handles admin notices for plugin dependencies and pro features.
 *
 * @since 1.0.0
 */
class Notice_Manager {
	/**
	 * Class constructor.
	 *
	 * Initializes the admin notice functionality by hooking into WordPress admin_init.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'check_elementor' ) );
		add_action( 'admin_notices', array( $this, 'custom_iframe_activation_notice' ) );
		add_action( 'admin_notices', array( $this, 'custom_iframe_rating_notice' ) );
	}

	/**
	 * Check if Elementor is installed and activated.
	 *
	 * Adds an admin notice if Elementor is not active.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function check_elementor() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'elementor_missing_notice' ) );
		}
	}

	/**
	 * Display admin notice for missing Elementor dependency.
	 *
	 * Shows a notice with appropriate button to either install or activate Elementor
	 * based on its current installation status.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function elementor_missing_notice() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$elementor_installed = file_exists( WP_PLUGIN_DIR . '/elementor/elementor.php' );

		// translators: Text for the button to activate Elementor plugin.
		$button_text = $elementor_installed ?
			__( 'Activate Elementor Now', 'custom-iframe' ) :
			// translators: Text for the button to install Elementor plugin.
			__( 'Install Elementor Now', 'custom-iframe' );

		$button_url = $elementor_installed ?
			wp_nonce_url(
				admin_url( 'plugins.php?action=activate&plugin=elementor/elementor.php' ),
				'activate-plugin_elementor/elementor.php'
			) :
			wp_nonce_url(
				self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ),
				'install-plugin_elementor'
			);

		printf(
			'<div class="notice notice-error is-dismissible"><p>%s</p><p><a href="%s" class="button-primary">%s</a></p></div>',
			// translators: Notice text shown when Elementor is not installed or activated.
			esc_html__(
				'Custom iFrame Widget requires Elementor to be installed and active.',
				'custom-iframe'
			),
			esc_url( $button_url ),
			esc_html( $button_text )
		);
	}

	/**
	 * Show admin notice after plugin activation.
	 *
	 * @since 1.0.4
	 */
	public function custom_iframe_activation_notice() {
		// Check if user has dismissed the notice.
		if ( get_user_meta( get_current_user_id(), '_custom_iframe_notice_dismissed', true ) ) {
			return;
		}
		echo '
		<style>
			.custom-iframe-notice {
				border-left: 4px solid #4f46e5;
				background: #ffffff;
				padding: 15px 20px;
				margin-top: 20px;
				border-radius: 6px;
				box-shadow: 0 2px 4px rgba(0,0,0,0.04);
			}
			.custom-iframe-notice h2 {
				margin: 0 0 8px;
				color: #2271b1;
			}
			.custom-iframe-notice a {
				text-decoration: none;
				color: #007cba;
				font-weight: 600;
			}
			.custom-iframe-notice a:hover {
				color: #00a0d2;
			}
		</style>
		<div class="notice notice-success is-dismissible custom-iframe-notice" data-dismissible="custom-iframe-notice">
		<h2>✅ ' . esc_html__( 'Custom iFrame Activated!', 'custom-iframe' ) . '</h2>
		<p>' . esc_html__( 'Edit a page with Elementor, search for "Custom iFrame" in the widget panel, and drag it into your layout.', 'custom-iframe' ) . '</p>
		<p>
			<a href="https://youtu.be/EB6MgWB6zLA?si=IqG88NkkM_DC84Ds" target="_blank">🎥 Video Tutorial</a> &nbsp;|&nbsp; 
			<a href="https://coderzstudio.com/plugins/custom-iframe/demo/?utm_source=wordpress&utm_medium=dashboard&utm_campaign=custom_iframe&utm_id=wp_03" target="_blank">🔗 Live Demo</a>
		</p>
	</div>';
	}

	/**
	 * Show rating notice after 14 days of installation.
	 *
	 * @since 1.0.10
	 */
	public function custom_iframe_rating_notice() {
		// Check if user has dismissed the notice permanently.
		if ( get_user_meta( get_current_user_id(), '_custif_rating_notice_dismissed', 1 ) ) {
			return;
		}

		// Get installation date.
		$installation_date = get_option( 'custif_installation_date' );
		if ( ! $installation_date ) {
			$installation_date = current_time( 'timestamp' );
			update_option( 'custif_installation_date', $installation_date );
			return;
		}

		// Check if it's been 14 days since installation.
		$days_since_installation = ( current_time( 'timestamp' ) - $installation_date ) / DAY_IN_SECONDS;
		if ( $days_since_installation < 14 ) {
			return;
		}

		// Check if user has clicked "Remind me later".
		$remind_later = get_user_meta( get_current_user_id(), '_custif_rating_remind_later', true );
		if ( $remind_later ) {
			$remind_later_date = strtotime( $remind_later );
			if ( ( current_time( 'timestamp' ) - $remind_later_date ) < ( 14 * DAY_IN_SECONDS ) ) {
				return;
			}
		}

		?>
		<style>
			.custif-rating-notice {
				border-left: 4px solid #4f46e5;
				background: #ffffff;
				padding: 15px 20px;
				margin-top: 20px;
				border-radius: 6px;
				box-shadow: 0 2px 4px rgba(0,0,0,0.04);
			}
			.custif-rating-notice h2 {
				margin: 0 0 8px;
				color: #2271b1;
			}
			.custif-rating-notice .notice-actions {
				margin-top: 12px;
			}
			.custif-rating-notice .notice-actions a {
				text-decoration: none;
				margin-right: 12px;
				font-weight: 600;
			}
			.custif-rating-notice .notice-actions .button-primary {
				background: #4f46e5;
				border-color: #4f46e5;
			}
			.custif-rating-notice .notice-actions .button-secondary {
				color: #4f46e5;
			}
		</style>
		<div class="notice notice-success is-dismissible custif-rating-notice" data-dismissible="custif-rating-notice">
			<h2>🎉 <?php esc_html_e( 'Enjoying Custom iFrame?', 'custom-iframe' ); ?></h2>
			<p><?php esc_html_e( 'Hey there! 👋 We\'ve noticed you\'ve been using Custom iFrame for a while now. We\'re thrilled to see you\'re making the most of our plugin!', 'custom-iframe' ); ?></p>
			<p><?php esc_html_e( 'Would you mind taking a moment to share your experience? Your feedback helps us improve and helps other users discover Custom iFrame.', 'custom-iframe' ); ?></p>
			<div class="notice-actions">
				<a href="https://wordpress.org/support/plugin/custom-iframe/reviews/?filter=5" target="_blank" class="button button-primary">
					<?php esc_html_e( '⭐ Rate 5 Stars', 'custom-iframe' ); ?>
				</a>
				<a href="#" class="button button-secondary custif-remind-later">
					<?php esc_html_e( '⏰ Remind Me Later', 'custom-iframe' ); ?>
				</a>
				<a href="#" class="button button-secondary custif-dismiss-rating">
					<?php esc_html_e( '❌ No, Thanks', 'custom-iframe' ); ?>
				</a>
			</div>
		</div>
		<?php
	}
}

new Notice_Manager();
