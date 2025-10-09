<?php
/**
 * Brand Assets Settings Class
 *
 * @since 0.1.0
 * @package BrandAssets
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Brand Assets Settings Class
 *
 * @since 0.1.0
 */
final class Brand_Assets_Settings {

	/**
	 * Settings group name
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private const SETTINGS_GROUP = 'brand_assets_settings';

	/**
	 * Options name
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private const OPTIONS_NAME = 'brand_assets_options';

	/**
	 * Constructor
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @since 0.1.0
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * Register plugin settings
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTIONS_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_options' ),
				'default'           => $this->get_default_options(),
			)
		);
	}

	/**
	 * Add admin menu page
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'Brand Assets settings', 'brand-assets' ),
			__( 'Brand Assets', 'brand-assets' ),
			'manage_options',
			'brand-assets-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Sanitize and validate plugin options
	 *
	 * @since 0.1.0
	 * @param array $input Raw input data from the settings form.
	 * @return array Sanitized and validated options array.
	 */
	public function sanitize_options( array $input ) {
		// Verify nonce for security.
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), self::SETTINGS_GROUP . '-options' ) ) {
			add_settings_error(
				self::SETTINGS_GROUP,
				'nonce_failed',
				__( 'Security check failed. Please try again.', 'brand-assets' )
			);
			return $this->get_options(); // Return current options on failure.
		}

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			add_settings_error(
				self::SETTINGS_GROUP,
				'capability_failed',
				__( 'You do not have sufficient permissions to save these settings.', 'brand-assets' )
			);
			return $this->get_options(); // Return current options on failure.
		}

		$sanitized = array();

		// Sanitize page ID.
		$sanitized['brand_page_id'] = absint( $input['brand_page_id'] ?? 0 );

		// Sanitize text fields.
		$sanitized['popover_heading']          = sanitize_text_field( $input['popover_heading'] ?? '' );
		$sanitized['popover_subheading_line1'] = sanitize_text_field( $input['popover_subheading_line1'] ?? '' );
		$sanitized['popover_subheading_line2'] = sanitize_text_field( $input['popover_subheading_line2'] ?? '' );
		$sanitized['popover_link_text']        = sanitize_text_field( $input['popover_link_text'] ?? '' );

		// Sanitize selector.
		$sanitized['logo_selector'] = sanitize_text_field( $input['logo_selector'] ?? '.wp-block-site-logo' );

		return $sanitized;
	}

	/**
	 * Get default options
	 *
	 * @since 0.1.0
	 * @return array Default options array.
	 */
	private function get_default_options() {
		return array(
			'brand_page_id'            => 0,
			'popover_heading'          => __( 'Looking for our logo?', 'brand-assets' ),
			'popover_subheading_line1' => __( "You're in the right spot!", 'brand-assets' ),
			'popover_subheading_line2' => __( 'Go to our', 'brand-assets' ),
			'popover_link_text'        => __( 'logo & style page', 'brand-assets' ),
			'logo_selector'            => '.wp-block-site-logo',
		);
	}

	/**
	 * Get current options
	 *
	 * @since 0.1.0
	 * @return array Current options array.
	 */
	public function get_options() {
		return get_option( self::OPTIONS_NAME, $this->get_default_options() );
	}

	/**
	 * Render settings page HTML
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function render_settings_page() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'brand-assets' ) );
		}

		$options = $this->get_options();
		$pages   = get_pages( array( 'post_status' => 'publish' ) );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( self::SETTINGS_GROUP );
				do_settings_sections( self::SETTINGS_GROUP );
				?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="brand_page_id"><?php esc_html_e( 'Brand Assets page', 'brand-assets' ); ?></label>
						</th>
						<td>
							<select name="<?php echo esc_attr( self::OPTIONS_NAME ); ?>[brand_page_id]" id="brand_page_id">
								<option value="0"><?php esc_html_e( 'Select a page...', 'brand-assets' ); ?></option>
								<?php foreach ( $pages as $page ) { ?>
									<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $options['brand_page_id'], $page->ID ); ?>>
										<?php echo esc_html( $page->post_title ); ?>
									</option>
								<?php } ?>
							</select>
							<p class="description"><?php esc_html_e( 'Select the page where users will be taken when they click the logo popover link.', 'brand-assets' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="popover_heading"><?php esc_html_e( 'Popover heading', 'brand-assets' ); ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo esc_attr( self::OPTIONS_NAME ); ?>[popover_heading]" id="popover_heading" value="<?php echo esc_attr( $options['popover_heading'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'The main heading text in the popover.', 'brand-assets' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="popover_subheading_line1"><?php esc_html_e( 'Popover line 1', 'brand-assets' ); ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo esc_attr( self::OPTIONS_NAME ); ?>[popover_subheading_line1]" id="popover_subheading_line1" value="<?php echo esc_attr( $options['popover_subheading_line1'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'The first line of text that appears below the heading.', 'brand-assets' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="popover_subheading_line2"><?php esc_html_e( 'Popover line 2', 'brand-assets' ); ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo esc_attr( self::OPTIONS_NAME ); ?>[popover_subheading_line2]" id="popover_subheading_line2" value="<?php echo esc_attr( $options['popover_subheading_line2'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'The second line of text that appears before the link.', 'brand-assets' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="popover_link_text"><?php esc_html_e( 'Link text', 'brand-assets' ); ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo esc_attr( self::OPTIONS_NAME ); ?>[popover_link_text]" id="popover_link_text" value="<?php echo esc_attr( $options['popover_link_text'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'The text for the link that takes users to your brand assets page.', 'brand-assets' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="logo_selector"><?php esc_html_e( 'Logo selector', 'brand-assets' ); ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo esc_attr( self::OPTIONS_NAME ); ?>[logo_selector]" id="logo_selector" value="<?php echo esc_attr( $options['logo_selector'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'CSS selector for the logo element that will trigger the popover on right-click.', 'brand-assets' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
