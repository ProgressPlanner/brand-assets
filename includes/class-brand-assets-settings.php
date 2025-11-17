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
	 * Initialize WordPress hooks
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
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

		$options = Brand_Assets::get_instance()->options->get_all();
		$pages   = get_pages( array( 'post_status' => 'publish' ) );

		if ( ! $pages ) {
			$pages = array();
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( Brand_Assets_Options::SETTINGS_GROUP );
				do_settings_sections( Brand_Assets_Options::SETTINGS_GROUP );
				?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="brand_page_id"><?php esc_html_e( 'Brand Assets page', 'brand-assets' ); ?></label>
						</th>
						<td>
							<select name="<?php echo esc_attr( Brand_Assets_Options::OPTIONS_NAME ); ?>[brand_page_id]" id="brand_page_id">
								<option value="0"><?php esc_html_e( 'Select a page...', 'brand-assets' ); ?></option>
								<?php foreach ( $pages as $page ) { ?>
									<option value="<?php echo esc_attr( (string) $page->ID ); ?>" <?php selected( $options['brand_page_id'], $page->ID ); ?>>
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
							<input type="text" name="<?php echo esc_attr( Brand_Assets_Options::OPTIONS_NAME ); ?>[heading]" id="popover_heading" value="<?php echo esc_attr( $options['heading'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'The main heading text in the popover.', 'brand-assets' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="popover_text_line1"><?php esc_html_e( 'Popover text line 1', 'brand-assets' ); ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo esc_attr( Brand_Assets_Options::OPTIONS_NAME ); ?>[text_line1]" id="popover_text_line1" value="<?php echo esc_attr( $options['text_line1'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'The first line of text that appears below the heading.', 'brand-assets' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="popover_text_line2"><?php esc_html_e( 'Popover text line 2', 'brand-assets' ); ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo esc_attr( Brand_Assets_Options::OPTIONS_NAME ); ?>[text_line2]" id="popover_text_line2" value="<?php echo esc_attr( $options['text_line2'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'The second line of text that appears before the link.', 'brand-assets' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="popover_link_text"><?php esc_html_e( 'Link text', 'brand-assets' ); ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo esc_attr( Brand_Assets_Options::OPTIONS_NAME ); ?>[link_text]" id="popover_link_text" value="<?php echo esc_attr( $options['link_text'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'The text for the link that takes users to your brand assets page.', 'brand-assets' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="logo_selector"><?php esc_html_e( 'Logo selector', 'brand-assets' ); ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo esc_attr( Brand_Assets_Options::OPTIONS_NAME ); ?>[logo_selector]" id="logo_selector" value="<?php echo esc_attr( $options['logo_selector'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'CSS selector for the logo element that will trigger the popover on right-click.', 'brand-assets' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="css_loading_mode"><?php esc_html_e( 'CSS loading mode', 'brand-assets' ); ?></label>
						</th>
						<td>
							<select name="<?php echo esc_attr( Brand_Assets_Options::OPTIONS_NAME ); ?>[css_loading_mode]" id="css_loading_mode">
								<option value="default" <?php selected( $options['css_loading_mode'], 'default' ); ?>><?php esc_html_e( 'Load the default CSS', 'brand-assets' ); ?></option>
								<option value="custom" <?php selected( $options['css_loading_mode'], 'custom' ); ?>><?php esc_html_e( 'Load custom CSS', 'brand-assets' ); ?></option>
								<option value="none" <?php selected( $options['css_loading_mode'], 'none' ); ?>><?php esc_html_e( 'Load no CSS', 'brand-assets' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Choose how CSS should be loaded for the popover.', 'brand-assets' ); ?></p>
						</td>
					</tr>

					<tr id="custom_css_row" style="<?php echo ( $options['css_loading_mode'] === 'custom' ) ? '' : 'display: none;'; ?>">
						<th scope="row">
							<label for="popover_css"><?php esc_html_e( 'Custom popover CSS', 'brand-assets' ); ?></label>
						</th>
						<td>
							<textarea name="<?php echo esc_attr( Brand_Assets_Options::OPTIONS_NAME ); ?>[css]" id="popover_css" rows="10" cols="50" class="large-text code"><?php echo esc_textarea( $options['css'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Custom CSS for styling the popover.', 'brand-assets' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>

		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const cssLoadingMode = document.getElementById('css_loading_mode');
			const customCssRow = document.getElementById('custom_css_row');

			if (cssLoadingMode && customCssRow) {
				function toggleCustomCssRow() {
					if (cssLoadingMode.value === 'custom') {
						customCssRow.style.display = '';
					} else {
						customCssRow.style.display = 'none';
					}
				}

				cssLoadingMode.addEventListener('change', toggleCustomCssRow);
				toggleCustomCssRow(); // Initialize on page load
			}
		});
		</script>
		<?php
	}
}
