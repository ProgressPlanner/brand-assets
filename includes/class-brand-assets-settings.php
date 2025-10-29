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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
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
	 * Enqueue admin scripts
	 *
	 * @since 0.1.0
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Only load on our settings page.
		if ( 'settings_page_brand-assets-settings' !== $hook ) {
			return;
		}

		// Enqueue WordPress color picker.
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Enqueue our admin script.
		wp_enqueue_script(
			'brand-assets-admin',
			plugins_url( 'assets/admin.js', __DIR__ ),
			array( 'jquery', 'wp-color-picker' ),
			BRAND_ASSETS_VERSION,
			true
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
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( Brand_Assets_Options::SETTINGS_GROUP );
				do_settings_sections( Brand_Assets_Options::SETTINGS_GROUP );
				?>

				<table class="form-table" role="presentation">
					<?php
					// Build options array for pages.
					$page_options = array(
						array(
							'value' => '0',
							'label' => __( 'Select a page...', 'brand-assets' ),
						),
					);
					foreach ( $pages as $page ) {
						$page_options[] = array(
							'value' => $page->ID,
							'label' => $page->post_title,
						);
					}

					$this->render_table_row(
						array(
							'label'       => __( 'Brand Assets page', 'brand-assets' ),
							'field_id'    => 'brand_page_id',
							'field'       => function () use ( $page_options, $options ) {
								$this->render_select_field(
									array(
										'name' => Brand_Assets_Options::OPTIONS_NAME . '[brand_page_id]',
										'id'   => 'brand_page_id',
									),
									$page_options,
									$options['brand_page_id']
								);
							},
							'description' => __( 'Select the page where users will be taken when they click the logo popover link.', 'brand-assets' ),
						)
					);
					?>

					<?php
					$this->render_table_row(
						array(
							'label'       => __( 'Popover heading', 'brand-assets' ),
							'field_id'    => 'popover_heading',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'text',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[heading]',
										'id'    => 'popover_heading',
										'value' => $options['heading'],
										'class' => 'regular-text',
									)
								);
							},
							'description' => __( 'The main heading text in the popover.', 'brand-assets' ),
						)
					);

					$this->render_table_row(
						array(
							'label'       => __( 'Popover text line 1', 'brand-assets' ),
							'field_id'    => 'popover_text_line1',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'text',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[text_line1]',
										'id'    => 'popover_text_line1',
										'value' => $options['text_line1'],
										'class' => 'regular-text',
									)
								);
							},
							'description' => __( 'The first line of text that appears below the heading.', 'brand-assets' ),
						)
					);

					$this->render_table_row(
						array(
							'label'       => __( 'Popover text line 2', 'brand-assets' ),
							'field_id'    => 'popover_text_line2',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'text',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[text_line2]',
										'id'    => 'popover_text_line2',
										'value' => $options['text_line2'],
										'class' => 'regular-text',
									)
								);
							},
							'description' => __( 'The second line of text that appears before the link.', 'brand-assets' ),
						)
					);

					$this->render_table_row(
						array(
							'label'       => __( 'Link text', 'brand-assets' ),
							'field_id'    => 'popover_link_text',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'text',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[link_text]',
										'id'    => 'popover_link_text',
										'value' => $options['link_text'],
										'class' => 'regular-text',
									)
								);
							},
							'description' => __( 'The text for the link that takes users to your brand assets page.', 'brand-assets' ),
						)
					);
					?>

					<?php
					$this->render_table_row(
						array(
							'label'       => __( 'Logo selector', 'brand-assets' ),
							'field_id'    => 'logo_selector',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'text',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[logo_selector]',
										'id'    => 'logo_selector',
										'value' => $options['logo_selector'],
										'class' => 'regular-text',
									)
								);
							},
							'description' => __( 'CSS selector for the logo element that will trigger the popover on right-click.', 'brand-assets' ),
						)
					);

					$this->render_table_row(
						array(
							'label'       => __( 'CSS loading mode', 'brand-assets' ),
							'field_id'    => 'css_loading_mode',
							'field'       => function () use ( $options ) {
								$this->render_select_field(
									array(
										'name' => Brand_Assets_Options::OPTIONS_NAME . '[css_loading_mode]',
										'id'   => 'css_loading_mode',
									),
									array(
										array(
											'value' => 'default',
											'label' => __( 'Load the default CSS', 'brand-assets' ),
										),
										array(
											'value' => 'none',
											'label' => __( 'Load no CSS', 'brand-assets' ),
										),
									),
									$options['css_loading_mode']
								);
							},
							'description' => __( 'Choose how CSS should be loaded for the popover. Select "Load no CSS" if you want to add custom CSS via your theme.', 'brand-assets' ),
						)
					);
					?>
				</table>

				<div id="popover-styling-section">
					<h2><?php esc_html_e( 'Popover Styling', 'brand-assets' ); ?></h2>
					<p><?php esc_html_e( 'Customize the appearance of the logo popover.', 'brand-assets' ); ?></p>

					<table class="form-table" role="presentation">
					<?php
					$this->render_table_row(
						array(
							'label'       => __( 'Background color', 'brand-assets' ),
							'field_id'    => 'popover_bg_color',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'text',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[popover_bg_color]',
										'id'    => 'popover_bg_color',
										'value' => $options['popover_bg_color'],
										'class' => 'brand-assets-color-picker',
									)
								);
							},
							'description' => __( 'Background color of the popover.', 'brand-assets' ),
						)
					);

					$this->render_table_row(
						array(
							'label'       => __( 'Text color', 'brand-assets' ),
							'field_id'    => 'popover_text_color',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'text',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[popover_text_color]',
										'id'    => 'popover_text_color',
										'value' => $options['popover_text_color'],
										'class' => 'brand-assets-color-picker',
									)
								);
							},
							'description' => __( 'Text color in the popover.', 'brand-assets' ),
						)
					);

					$this->render_table_row(
						array(
							'label'       => __( 'Link color', 'brand-assets' ),
							'field_id'    => 'popover_link_color',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'text',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[popover_link_color]',
										'id'    => 'popover_link_color',
										'value' => $options['popover_link_color'],
										'class' => 'brand-assets-color-picker',
									)
								);
							},
							'description' => __( 'Color of links in the popover.', 'brand-assets' ),
						)
					);

					$this->render_table_row(
						array(
							'label'       => __( 'Border color', 'brand-assets' ),
							'field_id'    => 'popover_border_color',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'text',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[popover_border_color]',
										'id'    => 'popover_border_color',
										'value' => $options['popover_border_color'],
										'class' => 'brand-assets-color-picker',
									)
								);
							},
							'description' => __( 'Border color of the popover.', 'brand-assets' ),
						)
					);

					$this->render_table_row(
						array(
							'label'       => __( 'Close button color', 'brand-assets' ),
							'field_id'    => 'popover_close_btn_color',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'text',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[popover_close_btn_color]',
										'id'    => 'popover_close_btn_color',
										'value' => $options['popover_close_btn_color'],
										'class' => 'brand-assets-color-picker',
									)
								);
							},
							'description' => __( 'Color of the close button.', 'brand-assets' ),
						)
					);
					?>

					<?php
					$this->render_table_row(
						array(
							'label'       => __( 'Border width (px)', 'brand-assets' ),
							'field_id'    => 'popover_border_width',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'number',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[popover_border_width]',
										'id'    => 'popover_border_width',
										'value' => $options['popover_border_width'],
										'min'   => '0',
										'max'   => '20',
										'class' => 'small-text',
									)
								);
							},
							'description' => __( 'Border width in pixels.', 'brand-assets' ),
						)
					);

					$this->render_table_row(
						array(
							'label'       => __( 'Border radius (px)', 'brand-assets' ),
							'field_id'    => 'popover_border_radius',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'number',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[popover_border_radius]',
										'id'    => 'popover_border_radius',
										'value' => $options['popover_border_radius'],
										'min'   => '0',
										'max'   => '50',
										'class' => 'small-text',
									)
								);
							},
							'description' => __( 'Border radius in pixels for rounded corners.', 'brand-assets' ),
						)
					);

					$this->render_table_row(
						array(
							'label'       => __( 'Padding (px)', 'brand-assets' ),
							'field_id'    => 'popover_padding',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'number',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[popover_padding]',
										'id'    => 'popover_padding',
										'value' => $options['popover_padding'],
										'min'   => '0',
										'max'   => '100',
										'class' => 'small-text',
									)
								);
							},
							'description' => __( 'Internal padding in pixels.', 'brand-assets' ),
						)
					);

					$this->render_table_row(
						array(
							'label'       => __( 'Max width (px)', 'brand-assets' ),
							'field_id'    => 'popover_max_width',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'number',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[popover_max_width]',
										'id'    => 'popover_max_width',
										'value' => $options['popover_max_width'],
										'min'   => '200',
										'max'   => '800',
										'class' => 'small-text',
									)
								);
							},
							'description' => __( 'Maximum width of the popover in pixels.', 'brand-assets' ),
						)
					);

					$this->render_table_row(
						array(
							'label'       => __( 'Font size (px)', 'brand-assets' ),
							'field_id'    => 'popover_font_size',
							'field'       => function () use ( $options ) {
								$this->render_input_field(
									array(
										'type'  => 'number',
										'name'  => Brand_Assets_Options::OPTIONS_NAME . '[popover_font_size]',
										'id'    => 'popover_font_size',
										'value' => $options['popover_font_size'],
										'min'   => '10',
										'max'   => '30',
										'class' => 'small-text',
									)
								);
							},
							'description' => __( 'Font size in pixels.', 'brand-assets' ),
						)
					);
					?>
					</table>
				</div>

				<div id="popover-styling-notice" style="display:none;">
					<h2><?php esc_html_e( 'Popover Styling', 'brand-assets' ); ?></h2>
					<div class="notice notice-info inline">
						<p><?php esc_html_e( 'Popover styling options are only available when "Load the default CSS" is selected. To use custom styling, select "Load the default CSS" above, or provide your own CSS via your theme.', 'brand-assets' ); ?></p>
					</div>
				</div>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render an input field.
	 *
	 * @param array $attrs The attributes for the input field.
	 * @return void
	 */
	protected function render_input_field( $attrs ) {
		echo '<input ';
		foreach ( $attrs as $attr => $value ) {
			echo esc_attr( $attr ) . '="' . esc_attr( $value ) . '" ';
		}
		echo '/>';
	}

	/**
	 * Render a select field.
	 *
	 * @param array $attrs The attributes for the select field.
	 * @param array $options The options for the select field.
	 * @param mixed $selected The selected value.
	 * @return void
	 */
	protected function render_select_field( $attrs, $options, $selected = null ) {
		echo '<select ';
		foreach ( $attrs as $attr => $value ) {
			echo esc_attr( $attr ) . '="' . esc_attr( $value ) . '" ';
		}
		echo '>';
		foreach ( $options as $option ) {
			echo '<option value="' . esc_attr( $option['value'] ) . '"';
			if ( null !== $selected && (string) $option['value'] === (string) $selected ) {
				echo ' selected="selected"';
			}
			echo '>' . esc_html( $option['label'] ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Render a table row with label, field, and description.
	 *
	 * @param array $args {
	 *     Arguments for rendering the table row.
	 *
	 *     @type string   $label       The label text for the field.
	 *     @type string   $field_id    The ID attribute for the field (used for the label's "for" attribute).
	 *     @type callable $field       A callback function that renders the field (input, select, etc.).
	 *     @type string   $description Optional. The description text below the field.
	 * }
	 * @return void
	 */
	protected function render_table_row( $args ) {
		$defaults = array(
			'label'       => '',
			'field_id'    => '',
			'field'       => null,
			'description' => '',
		);
		$args     = wp_parse_args( $args, $defaults );

		echo '<tr>';
		echo '<th scope="row">';
		if ( ! empty( $args['label'] ) && ! empty( $args['field_id'] ) ) {
			echo '<label for="' . esc_attr( $args['field_id'] ) . '">' . esc_html( $args['label'] ) . '</label>';
		}
		echo '</th>';
		echo '<td>';
		if ( is_callable( $args['field'] ) ) {
			call_user_func( $args['field'] );
		}
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
		echo '</td>';
		echo '</tr>';
	}
}
