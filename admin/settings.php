<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class VI_WPRODUCTBUILDER_Admin_Settings {
	protected $languages_count;
	protected $languages;
	protected $default_language;
	protected $languages_data;

	public function __construct() {
		$this->languages_count  = 0;
		$this->languages        = array();
		$this->languages_data   = array();
		$this->default_language = '';
		add_action( 'admin_menu', array( $this, 'setting_menu' ), 22 );
		add_action( 'admin_init', array( $this, 'save_data' ) );
	}

	public static function set_option_field( $field, $multi = false ) {
		if ( $field ) {
			if ( $multi ) {
				return 'woopb_option-param[' . $field . '][]';
			} else {
				return 'woopb_option-param[' . $field . ']';
			}

		} else {
			return '';
		}
	}

	public static function get_option_field( $field, $default = '' ) {
		$params = get_option( 'woopb_option-param', array() );
		if ( isset( $params[ $field ] ) && $field ) {
			return $params[ $field ];
		} else {
			return $default;
		}
	}

	public function save_data() {
		/**
		 * Check update
		 */
		$setting_url = admin_url( 'edit.php?post_type=woo_product_builder&page=woocommerce-product-builder-setting' );
		$key         = self::get_option_field( 'key' );
		new VillaTheme_Plugin_Check_Update (
			VI_WPRODUCTBUILDER_VERSION,                    // current version
			'https://villatheme.com/wp-json/downloads/v3',  // update path
			'woocommerce-product-builder/woocommerce-product-builder.php',                  // plugin file slug
			'woocommerce-product-builder', '8188', $key, $setting_url
		);
		new VillaTheme_Plugin_Updater( 'woocommerce-product-builder/woocommerce-product-builder.php', 'woocommerce-product-builder', $setting_url );

		/*Save setting options*/
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! isset( $_POST['_opt_woo_product_builder_nonce'] ) || ! wp_verify_nonce( $_POST['_opt_woo_product_builder_nonce'], 'opt_woo_product_builder_action_nonce' ) ) {
			return false;
		}

		$data = wc_clean( $_POST['woopb_option-param'] ?? [] );

		$data['custom_css'] = sanitize_textarea_field( wp_unslash( $data['custom_css'] ) );

		if ( isset( $_POST['message_body'] ) ) {
			$data['message_body'] = wp_kses_post( wp_unslash( $_POST['message_body'] ) );
		}

		if ( isset( $_POST['layout_header'] ) ) {
			$data['layout_header'] = wp_kses_post( wp_unslash( $_POST['layout_header'] ) );
		}

		if ( isset( $_POST['layout_footer'] ) ) {
			$data['layout_footer'] = wp_kses_post( wp_unslash( $_POST['layout_footer'] ) );
		}

		if ( isset( $_POST['woopb_option-param']['check_key'] ) ) {
			unset( $_POST['woopb_option-param']['check_key'] );
			delete_site_transient( 'update_plugins' );
			delete_transient( 'villatheme_item_8188' );
			delete_option( 'woocommerce-product-builder_messages' );
		}
		update_option( 'woopb_option-param', $data );
	}

	public function page_callback() {
		/*wpml*/
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			global $sitepress;
			$default_lang           = $sitepress->get_default_language();
			$this->default_language = $default_lang;
			$languages              = apply_filters( 'wpml_active_languages', null, null );
			$this->languages_data   = $languages;
			if ( count( $languages ) ) {
				foreach ( $languages as $key => $language ) {
					if ( $key != $default_lang ) {
						$this->languages[] = $key;
					}
				}
			}
		} elseif ( class_exists( 'Polylang' ) && function_exists( 'pll_languages_list' ) ) {
			/*Polylang*/
			$languages    = pll_languages_list();
			$default_lang = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';
			foreach ( $languages as $language ) {
				if ( $language == $default_lang ) {
					continue;
				}
				$this->languages[] = $language;
			}
		}
		$this->languages_count = count( $this->languages );

		?>
        <div class="wrap woocommerce-product-builder">
            <h2><?php esc_html_e( 'WooCommerce Product Builder Settings', 'woocommerce-product-builder' ) ?></h2>

            <form class="vi-ui form" method="post" action="">
				<?php
				wp_nonce_field( 'opt_woo_product_builder_action_nonce', '_opt_woo_product_builder_nonce' );
				settings_fields( 'woocommerce-product-builder' );
				do_settings_sections( 'woocommerce-product-builder' );
				?>
                <div class="vi-ui top attached tabular menu">
                    <a class="item active"
                       data-tab="design"><?php esc_html_e( 'Design', 'woocommerce-product-builder' ) ?></a>
                    <a class="item " data-tab="email"><?php esc_html_e( 'Email', 'woocommerce-product-builder' ) ?></a>
                    <a class="item " data-tab="print"><?php esc_html_e( 'Print & PDF', 'woocommerce-product-builder' ) ?></a>
                    <a class="item " data-tab="update"><?php esc_html_e( 'Update', 'woocommerce-product-builder' ) ?></a>
                </div>

                <!--Design-->
                <div class="vi-ui bottom attached tab segment active" data-tab="design">
                    <table class="form-table vi-ui form">
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo esc_attr( self::set_option_field( 'button_text_color' ) ) ?>">
									<?php esc_html_e( 'Template', 'woocommerce-product-builder' ) ?>
                                </label>
                            </th>
                            <td>

                                <div class="equal width fields woopb-wrap-option-frontend-style">
									<?php
									$list_option_frontend_style = [
										'classic'          => esc_html__( 'Classic', 'woocommerce-product-builder' ),
										'classic-layout-2' => esc_html__( 'Classic Layout 2', 'woocommerce-product-builder' ),
										'modern'           => esc_html__( 'Modern Layout 1', 'woocommerce-product-builder' ),
										'modern-layout-2'  => esc_html__( 'Modern Layout 2', 'woocommerce-product-builder' ),
										'modern-layout-3'  => esc_html__( 'Modern Layout 3', 'woocommerce-product-builder' ),
										'ajax-layout-1'    => esc_html__( 'AJAX Layout 1', 'woocommerce-product-builder' ),
										'ajax-layout-2'    => esc_html__( 'AJAX Layout 2', 'woocommerce-product-builder' ),
									];
									$selected                   = self::get_option_field( 'template', 'ajax-layout-1' );

									if ( trim( $selected ) === 'ajax' ) {
										$selected = 'ajax-layout-1';
									}/*Convert old value to new value*/
									foreach ( $list_option_frontend_style as $key => $value ) {
										$class_active = '';

										if ( $selected == $key ) {
											$class_active = 'woopb-option-active';
										}
										?>

                                        <div class="woopb-option-frontend-style">
                                            <input class="woopb-option-frontend-style-setting" type="radio"
                                                   name="<?php echo esc_attr( self::set_option_field( 'template' ) ); ?>" value="<?php echo esc_attr( $key ) ?>"
                                                   id="frontend-style-<?php echo esc_attr( $key ) ?>" <?php checked( $selected, $key ) ?>>
                                            <label for="frontend-style-<?php echo esc_attr( $key ) ?>" class="<?php echo esc_attr( $class_active ); ?>"><?php echo esc_html( $value ) ?></label>
                                            <div class="woopb-tooltip-image">
                                                <img src="<?php echo esc_url( VI_WPRODUCTBUILDER_IMAGES . $value . '.jpg' ); ?>" alt="<?php echo esc_attr( $value ) ?>">
                                            </div>
                                        </div>

										<?php
									}
									?>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div class="vi-ui styled fluid accordion">
                        <div class="title active">
                            <i class="dropdown icon"></i><?php esc_html_e( 'Button', 'woocommerce-product-builder' ); ?>
                        </div>
                        <div class="content active">
                            <p class="description"><?php esc_html_e( 'Set color and background color for WooCommerce Product Builder buttons.', 'woocommerce-product-builder' ); ?></p>
                            <table class="form-table vi-ui form">
                                <tr valign="top">
                                    <th scope="row">
                                        <label><?php esc_html_e( 'Design "Load step" button', 'woocommerce-product-builder' ) ?></label>
                                    </th>
                                    <td>
                                        <div class="vi-ui segment">
                                            <label class="vi-ui top attached label"><?php esc_html_e( 'Default', 'woocommerce-product-builder' ); ?></label>
                                            <div class="vi-ui vertical segment">
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <label><?php esc_html_e( '"Load step" button type', 'woocommerce-product-builder' ) ?></label>
                                                        <select name="<?php echo esc_attr( self::set_option_field( 'load_step_button_type' ) ); ?>" class="vi-ui dropdown fluid">
															<?php
															$selected = self::get_option_field( 'load_step_button_type', 'text' );
															$options  = [
																'icon'          => esc_html__( 'Icon', 'woocommerce-product-builder' ),
																'text'          => esc_html__( 'Text', 'woocommerce-product-builder' ),
																'icon_and_text' => esc_html__( 'Icon + Text', 'woocommerce-product-builder' )
															];
															foreach ( $options as $value => $text ) {
																printf( "<option value='%s' %s>%s</option>", esc_attr( $value ), selected( $selected, $value ), esc_html( $text ) );
															}
															?>
                                                        </select>
                                                        <p class="description"><?php esc_html_e( 'Choose how the "Load step" button will be displayed', 'woocommerce-product-builder' ); ?></p>
                                                    </div>
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'add_button_text' ) ) ?>"><?php esc_html_e( '"Add" button text', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="vi-ui input" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'add_button_text' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'add_button_text', 'Select' ) ); ?>"
                                                        >
                                                    </div>
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'add_more_button_text' ) ) ?>"><?php esc_html_e( '"Add more" button text', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="vi-ui input" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'add_more_button_text' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'add_more_button_text', 'Select More' ) ); ?>"
                                                        >
                                                    </div>
                                                </div>
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'load_step_button_text_color' ) ) ?>"><?php esc_html_e( 'Text color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'load_step_button_text_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'load_step_button_text_color', '#0b57d0' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'load_step_button_text_color', '#0b57d0' ) ); ?>">
                                                    </div>
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'load_step_button_bg_color' ) ) ?>"><?php esc_html_e( 'Background color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'load_step_button_bg_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'load_step_button_bg_color', '#ffffff' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'load_step_button_bg_color', '#ffffff' ) ); ?>">
                                                    </div>
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'load_step_button_border_width' ) ) ?>"><?php esc_html_e( 'Border width', 'woocommerce-product-builder' ) ?></label>
                                                        <div class="vi-ui right labeled fluid input">
                                                            <input type="number"
                                                                   name="<?php echo esc_attr( self::set_option_field( 'load_step_button_border_width' ) ); ?>"
                                                                   id="<?php echo esc_attr( self::set_option_field( 'load_step_button_border_width' ) ); ?>"
                                                                   placeholder="<?php esc_attr_e( 'Enter Width', 'woocommerce-product-builder' ); ?>"
                                                                   min="0"
                                                                   value="<?php echo esc_attr( self::get_option_field( 'load_step_button_border_width', 1 ) ); ?>"
                                                            >
                                                            <div class="vi-ui basic label"><?php esc_html_e( 'px', 'woocommerce-product-builder' ) ?></div>
                                                        </div>
                                                        <p class="description"><?php esc_html_e( 'If border is set, the border color will be taken from the text button color.', 'woocommerce-product-builder' ); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="vi-ui segment">
                                            <label class="vi-ui top attached label"><?php esc_html_e( 'Hover', 'woocommerce-product-builder' ); ?></label>
                                            <div class="vi-ui vertical segment">
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'load_step_button_hover_text_color' ) ) ?>"><?php esc_html_e( 'Text color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'load_step_button_hover_text_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'load_step_button_hover_text_color', '#0b57d0' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'load_step_button_hover_text_color', '#0b57d0' ) ); ?>">
                                                    </div>
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'load_step_button_hover_bg_color' ) ) ?>"><?php esc_html_e( 'Background color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'load_step_button_hover_bg_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'load_step_button_hover_bg_color', '#ffffff' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'load_step_button_hover_bg_color', '#ffffff' ) ); ?>">
                                                    </div>
                                                    <div class="field"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label><?php esc_html_e( 'Design Button', 'woocommerce-product-builder' ); ?></label>
                                    </th>
                                    <td>
                                        <div class="vi-ui segment">
                                            <label class="vi-ui top attached label"><?php esc_html_e( 'Default', 'woocommerce-product-builder' ); ?></label>
                                            <div class="vi-ui vertical segment">
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'button_icon' ) ) ?>"><?php esc_html_e( 'Button icon', 'woocommerce-product-builder' ) ?></label>
                                                        <select class="vi-ui dropdown"
                                                                name="<?php echo esc_attr( self::set_option_field( 'button_icon' ) ) ?>">
                                                            <option value="0" <?php selected( self::get_option_field( 'button_icon' ), 0 ) ?>><?php esc_html_e( 'Text', 'woocommerce-product-builder' ); ?></option>
                                                            <option value="1" <?php selected( self::get_option_field( 'button_icon' ), 1 ) ?>><?php esc_html_e( 'Icon', 'woocommerce-product-builder' ); ?></option>
                                                        </select>
                                                        <p class="description"><?php esc_html_e( 'If you use AJAX template and icon option is selected: send email, get share link, print, pdf button will use icon instead', 'woocommerce-product-builder' ); ?></p>
                                                    </div>
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'button_text_color' ) ) ?>"><?php esc_html_e( 'Text color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'button_text_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'button_text_color', '#fff' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'button_text_color', '#fff' ) ); ?>">
                                                    </div>
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'button_bg_color' ) ) ?>"><?php esc_html_e( 'Background color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'button_bg_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'button_bg_color', '#04747a' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'button_bg_color', '#04747a' ) ); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="vi-ui segment">
                                            <label class="vi-ui top attached label"><?php esc_html_e( 'Button Primary', 'woocommerce-product-builder' ); ?></label>
                                            <div class="vi-ui vertical segment">
                                                <div class="equal width fields">
                                                    <div class="field"></div>
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'button_main_text_color' ) ) ?>"><?php esc_html_e( 'Text color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'button_main_text_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'button_main_text_color', '#fff' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'button_main_text_color', '#fff' ) ); ?>">
                                                    </div>
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'button_main_bg_color' ) ) ?>"><?php esc_html_e( 'Background color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'button_main_bg_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'button_main_bg_color', '#4b9989' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'button_main_bg_color', '#04747a' ) ); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label><?php esc_html_e( 'Design Preview page button', 'woocommerce-product-builder' ) ?></label>
                                    </th>
                                    <td>
                                        <div class="vi-ui segment">
                                            <label class="vi-ui top attached label"><?php esc_html_e( 'Default', 'woocommerce-product-builder' ); ?></label>
                                            <div class="vi-ui vertical segment">
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'preview_page_button_text_color' ) ) ?>"><?php esc_html_e( 'Text color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'preview_page_button_text_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'preview_page_button_text_color', '#0b57d0' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'load_step_button_text_color', '#0b57d0' ) ); ?>">
                                                    </div>
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'preview_page_button_bg_color' ) ) ?>"><?php esc_html_e( 'Background color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'preview_page_button_bg_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'preview_page_button_bg_color', '#ffffff' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'preview_page_button_bg_color', '#ffffff' ) ); ?>">
                                                    </div>
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'preview_page_button_border_width' ) ) ?>"><?php esc_html_e( 'Border width', 'woocommerce-product-builder' ) ?></label>
                                                        <div class="vi-ui right labeled fluid input">
                                                            <input type="number"
                                                                   name="<?php echo esc_attr( self::set_option_field( 'preview_page_button_border_width' ) ); ?>"
                                                                   id="<?php echo esc_attr( self::set_option_field( 'preview_page_button_border_width' ) ); ?>"
                                                                   placeholder="<?php esc_attr_e( 'Enter Width', 'woocommerce-product-builder' ); ?>"
                                                                   min="0"
                                                                   value="<?php echo esc_attr( self::get_option_field( 'preview_page_button_border_width', 1 ) ); ?>"
                                                            >
                                                            <div class="vi-ui basic label"><?php esc_html_e( 'px', 'woocommerce-product-builder' ) ?></div>
                                                        </div>
                                                        <p class="description"><?php esc_html_e( 'If border is set, the border color will be taken from the text button color.', 'woocommerce-product-builder' ); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="vi-ui segment">
                                            <label class="vi-ui top attached label"><?php esc_html_e( 'Hover', 'woocommerce-product-builder' ); ?></label>
                                            <div class="vi-ui vertical segment">
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'preview_page_button_hover_text_color' ) ) ?>"><?php esc_html_e( 'Text color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'preview_page_button_hover_text_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'preview_page_button_hover_text_color', '#0b57d0' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'preview_page_button_hover_text_color', '#0b57d0' ) ); ?>">
                                                    </div>
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'preview_page_button_hover_bg_color' ) ) ?>"><?php esc_html_e( 'Background color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'preview_page_button_hover_bg_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'preview_page_button_hover_bg_color', '#ffffff' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'preview_page_button_hover_bg_color', '#ffffff' ) ); ?>">
                                                    </div>
                                                    <div class="field"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="title active">
                            <i class="dropdown icon"></i><?php esc_html_e( 'Mobile', 'woocommerce-product-builder' ); ?>
                        </div>
                        <div class="content active">
                            <table class="form-table vi-ui form">
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="<?php echo esc_attr( self::set_option_field( 'mobile_bar_position' ) ) ?>">
											<?php esc_html_e( 'Distance from bottom', 'woocommerce-product-builder' ) ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input class="" type="number" min="0" step="1"
                                               name="<?php echo esc_attr( self::set_option_field( 'mobile_bar_position' ) ); ?>"
                                               value="<?php echo esc_attr( self::get_option_field( 'mobile_bar_position', 0 ) ); ?>">
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for=""><?php esc_html_e( 'Control bar', 'woocommerce-product-builder' ) ?></label>
                                    </th>
                                    <td>
                                        <div class="vi-ui segment">
                                            <div class="vi-ui vertical segment">
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'mobile_bar_text_color' ) ) ?>"><?php esc_html_e( 'Text color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'mobile_bar_text_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'mobile_bar_text_color', '#000' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'mobile_bar_text_color', '#000' ) ); ?>">
                                                    </div>
                                                    <div class="field">
                                                        <label for="<?php echo esc_attr( self::set_option_field( 'mobile_bar_bg_color' ) ) ?>"><?php esc_html_e( 'Background color', 'woocommerce-product-builder' ) ?></label>
                                                        <input class="color-picker" type="text"
                                                               name="<?php echo esc_attr( self::set_option_field( 'mobile_bar_bg_color' ) ); ?>"
                                                               value="<?php echo esc_attr( self::get_option_field( 'mobile_bar_bg_color', '#fff' ) ); ?>"
                                                               style="background-color: <?php echo esc_attr( self::get_option_field( 'mobile_bar_bg_color', '#fff' ) ); ?>">
                                                    </div>


                                                </div>
                                            </div>
                                        </div>

                                    </td>
                                </tr>

                            </table>
                        </div>
                        <div class="title active">
                            <i class="dropdown icon"></i><?php esc_html_e( 'Advanced', 'woocommerce-product-builder' ); ?>
                        </div>
                        <div class="content active">
                            <table class="form-table vi-ui form">
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="<?php echo esc_attr( self::set_option_field( 'share_link' ) ) ?>">
											<?php esc_html_e( 'Display share link', 'woocommerce-product-builder' ); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <div class="vi-ui checkbox toggle">
                                            <input type="checkbox" id="<?php echo esc_attr( self::set_option_field( 'share_link' ) ) ?>"
                                                   name="<?php echo esc_attr( self::set_option_field( 'share_link' ) ) ?>"
                                                   value="1" <?php checked( self::get_option_field( 'share_link' ), 1 ) ?>>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="<?php echo esc_attr( self::set_option_field( 'get_short_share_link' ) ) ?>">
											<?php esc_html_e( 'Display get short share link for customer', 'woocommerce-product-builder' ); ?>
                                        </label>
                                        <p class="description"><?php esc_html_e( 'Default: Display for admin', 'woocommerce-product-builder' ); ?></p>
                                    </th>
                                    <td>
                                        <div class="vi-ui checkbox toggle">
                                            <input type="checkbox" id="<?php echo esc_attr( self::set_option_field( 'get_short_share_link' ) ) ?>"
                                                   name="<?php echo esc_attr( self::set_option_field( 'get_short_share_link' ) ) ?>"
                                                   value="1" <?php checked( self::get_option_field( 'get_short_share_link' ), 1 ) ?>>
                                        </div>
                                    </td>
                                </tr>
                                 <tr valign="top">
                                    <th scope="row">
                                        <label for="<?php echo esc_attr( self::set_option_field( 'remember_modal_state' ) ) ?>">
											<?php esc_html_e( ' Remember Modal State', 'woocommerce-product-builder' ); ?>
                                        </label>
                                        <p class="description"><?php esc_html_e( 'Keep the modal’s open or closed state after page reload. Default: Disabled', 'woocommerce-product-builder' ); ?></p>
                                    </th>
                                    <td>
                                        <div class="vi-ui checkbox toggle">
                                            <input type="checkbox" id="<?php echo esc_attr( self::set_option_field( 'remember_modal_state' ) ) ?>"
                                                   name="<?php echo esc_attr( self::set_option_field( 'remember_modal_state' ) ) ?>"
                                                   value="1" <?php checked( self::get_option_field( 'remember_modal_state' ), 1 ) ?>>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="<?php echo esc_attr( self::set_option_field( 'time_to_remove_short_share_link' ) ) ?>">
											<?php esc_html_e( 'Remove short share link records after x day(s)', 'woocommerce-product-builder' ); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="text" name="<?php echo esc_attr( self::set_option_field( 'time_to_remove_short_share_link' ) ); ?>"
                                               value="<?php echo esc_attr( self::get_option_field( 'time_to_remove_short_share_link', 30 ) ); ?>">
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="<?php echo esc_attr( self::set_option_field( 'remove_session' ) ) ?>">
											<?php esc_html_e( 'Clear session', 'woocommerce-product-builder' ); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <div class="vi-ui checkbox toggle">
                                            <input type="checkbox" id="<?php echo esc_attr( self::set_option_field( 'remove_session' ) ) ?>"
                                                   name="<?php echo esc_attr( self::set_option_field( 'remove_session' ) ) ?>"
                                                   value="1" <?php checked( self::get_option_field( 'remove_session' ), 1 ) ?>>
                                        </div>
                                        <p class="description"><?php esc_html_e( 'Clear session after add to cart', 'woocommerce-product-builder' ); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="<?php echo esc_attr( self::set_option_field( 'clear_filter' ) ) ?>">
											<?php esc_html_e( 'Clear filter', 'woocommerce-product-builder' ); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <div class="vi-ui checkbox toggle">
                                            <input type="checkbox" id="<?php echo esc_attr( self::set_option_field( 'clear_filter' ) ) ?>"
                                                   name="<?php echo esc_attr( self::set_option_field( 'clear_filter' ) ) ?>"
                                                   value="1" <?php checked( self::get_option_field( 'clear_filter' ), 1 ) ?>>
                                        </div>
                                        <p class="description"><?php esc_html_e( 'Clear filter after select', 'woocommerce-product-builder' ); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <table class="form-table vi-ui form">
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo esc_attr( self::set_option_field( 'custom_css' ) ) ?>"><?php esc_html_e( 'Custom CSS', 'woocommerce-product-builder' ); ?></label>
                            </th>
                            <td>
                                <textarea name="<?php echo esc_attr( self::set_option_field( 'custom_css' ) ) ?>"><?php echo esc_textarea( self::get_option_field( 'custom_css' ) ) ?></textarea>
                            </td>
                        </tr>
                    </table>
                </div>

                <!--Email Design-->
                <div class="vi-ui bottom attached tab segment " data-tab="email">
                    <table class="form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo esc_attr( self::set_option_field( 'enable_email' ) ) ?>"><?php esc_html_e( 'Enable', 'woocommerce-product-builder' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui checkbox toggle">
                                    <input type="checkbox" id="<?php echo esc_attr( self::set_option_field( 'enable_email' ) ) ?>"
                                           name="<?php echo esc_attr( self::set_option_field( 'enable_email' ) ) ?>"
                                           value="1" <?php checked( self::get_option_field( 'enable_email' ), 1 ) ?>>
                                </div>
                                <p class="description"><?php esc_html_e( 'Allow customers to send an email to friends on the preview page.', 'woocommerce-product-builder' ) ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo esc_attr( self::set_option_field( 'email_header' ) ) ?>"><?php esc_html_e( 'Header', 'woocommerce-product-builder' ) ?></label>
                            </th>
                            <td>
                                <div class="field">
                                    <input type="text" id="<?php echo esc_attr( self::set_option_field( 'email_header' ) ) ?>"
                                           name="<?php echo esc_attr( self::set_option_field( 'email_header' ) ) ?>"
                                           placeholder="<?php esc_html_e( 'WordPress', 'woocommerce-product-builder' ) ?>"
                                           value="<?php echo esc_attr( self::get_option_field( 'email_header', '' ) ) ?>">
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo esc_attr( self::set_option_field( 'email_from' ) ) ?>"><?php esc_html_e( 'From', 'woocommerce-product-builder' ) ?></label>
                            </th>
                            <td>
                                <div class="field">
									<?php $admin_email = get_option( 'admin_email' ); ?>
                                    <input type="email" id="<?php echo esc_attr( self::set_option_field( 'email_from' ) ) ?>"
                                           name="<?php echo esc_attr( self::set_option_field( 'email_from' ) ) ?>"
                                           placeholder="<?php esc_html_e( '<admin@yoursite.com>', 'woocommerce-product-builder' ) ?>"
                                           value="<?php echo esc_attr( self::get_option_field( 'email_from', $admin_email ) ) ?>"
                                           required>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo esc_attr( self::set_option_field( 'email_subject' ) ) ?>"><?php esc_html_e( 'Subject', 'woocommerce-product-builder' ) ?></label>
                            </th>
                            <td>
                                <div class="field">
                                    <input type="text" id="<?php echo esc_attr( self::set_option_field( 'email_subject' ) ) ?>"
                                           name="<?php echo esc_attr( self::set_option_field( 'email_subject' ) ) ?>"
                                           placeholder="<?php esc_html_e( '[Subject email]', 'woocommerce-product-builder' ) ?>"
                                           value="<?php echo esc_attr( self::get_option_field( 'email_subject' ) ) ?>">
                                </div>
								<?php
								if ( count( $this->languages ) ) {
									foreach ( $this->languages as $key => $value ) {

										?>
                                        <p>
                                            <label for="<?php echo esc_attr( self::set_option_field( 'email_subject_' . $value ) ) ?>"><?php
												if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
													?>
                                                    <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
													<?php
												}
												echo esc_html( $value );
												if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
													echo esc_html( '(' . $this->languages_data[ $value ]['translated_name'] . ')' );
												}
												?>:</label>
                                        </p>
                                        <input type="text" id="<?php echo esc_attr( self::set_option_field( 'email_subject_' . $value ) ) ?>"
                                               name="<?php echo esc_attr( self::set_option_field( 'email_subject_' . $value ) ) ?>"
                                               placeholder="<?php esc_html_e( '[Subject email]', 'woocommerce-product-builder' ) ?>"
                                               value="<?php echo esc_attr( self::get_option_field( 'email_subject_' . $value ) ) ?>">
										<?php
									}
								}
								?>
                                <p class="description"><?php esc_html_e( 'The first text display on subject field of email.', 'woocommerce-product-builder' ) ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo esc_attr( self::set_option_field( 'message_body' ) ) ?>"><?php esc_html_e( 'Message Body', 'woocommerce-product-builder' ) ?></label>
                            </th>
                            <td>
                                <div class="field">
									<?php
									$default_content = "From: {email} \nSubject: {subject} \nMessage body: \n{message_content} \n{callback_link} \n\n-- \nThis e-mail was sent from a contact form on anonymous website (http://yoursite.com)";
									$content         = self::get_option_field( 'message_body', $default_content );
									$editor_id       = 'message_body';

									wp_editor( $content, $editor_id );

									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$default_content_langs = "From: {email} \nSubject: {subject} \nMessage body: \n{message_content} \n{callback_link} \n\n-- \nThis e-mail was sent from a contact form on anonymous website (http://yoursite.com)";
											$content_langs         = self::get_option_field( 'message_body_' . $value, $default_content_langs );
											$editor_id_langs       = 'message_body_' . $value;


											?>
                                            <p>
                                                <label for="<?php echo esc_attr( self::set_option_field( 'message_body_' . $value ) ) ?>"><?php
													if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo esc_html( $value );
													if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
														echo esc_html( '(' . $this->languages_data[ $value ]['translated_name'] . ')' );
													}
													?>:</label>
                                            </p>
											<?php
											wp_editor( $content_langs, $editor_id_langs );
										}
									}
									?>
                                </div>
                                <p class="description"><?php esc_html_e( 'The content of message.', 'woocommerce-product-builder' ) ?></p>
                                <ul class="description" style="list-style: none">
                                    <li>
                                        <span>{email}</span>
                                        - <?php esc_html_e( 'Your email.', 'woocommerce-product-builder' ) ?>
                                    </li>
                                    <li>
                                        <span>{subject}</span>
                                        - <?php esc_html_e( 'The subject of email.', 'woocommerce-product-builder' ) ?>
                                    </li>
                                    <li>
                                        <span>{message_content}</span>
                                        - <?php esc_html_e( 'The content of message body.', 'woocommerce-product-builder' ) ?>
                                    </li>
                                    <li>
                                        <span>{callback_link}</span>
                                        - <?php esc_html_e( 'Auto add product list when click link.', 'woocommerce-product-builder' ) ?>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo esc_attr( self::set_option_field( 'message_success' ) ) ?>"><?php esc_html_e( 'Message thank you', 'woocommerce-product-builder' ) ?></label>
                            </th>
                            <td>
                                <div class="field">
                                    <input type="text" id="<?php echo esc_attr( self::set_option_field( 'message_success' ) ) ?>"
                                           name="<?php echo esc_attr( self::set_option_field( 'message_success' ) ) ?>"
                                           value="<?php echo esc_attr( self::get_option_field( 'message_success', 'Thank you! Your email has sent to your friend!' ) ) ?>"/>
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											?>
                                            <p>
                                                <label for="<?php echo esc_attr( self::set_option_field( 'message_success_' . $value ) ) ?>"><?php
													if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo esc_html( $value );
													if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
														echo esc_html( '(' . $this->languages_data[ $value ]['translated_name'] . ')' );
													}
													?>:</label>
                                            </p>
                                            <input type="text" id="<?php echo esc_attr( self::set_option_field( 'message_success_' . $value ) ) ?>"
                                                   name="<?php echo esc_attr( self::set_option_field( 'message_success_' . $value ) ) ?>"
                                                   value="<?php echo esc_attr( self::get_option_field( 'message_success_' . $value, 'Thank you! Your email has sent to your friend!' ) ) ?>"/>
											<?php
										}
									}
									?>
                                </div>
                                <p class="description"><?php esc_html_e( 'The messages display after sent email.', 'woocommerce-product-builder' ) ?></p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!--				Print & PDF-->
                <div class="vi-ui bottom attached tab segment " data-tab="print">
                    <table class="form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo esc_attr( self::set_option_field( 'print_button' ) ) ?>">
									<?php esc_html_e( 'Print button', 'woocommerce-product-builder' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui checkbox toggle">
                                    <input type="checkbox" id="<?php echo esc_attr( self::set_option_field( 'print_button' ) ) ?>"
                                           name="<?php echo esc_attr( self::set_option_field( 'print_button' ) ) ?>"
                                           value="1" <?php checked( self::get_option_field( 'print_button' ), 1 ) ?>>
                                </div>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo esc_attr( self::set_option_field( 'download_pdf' ) ) ?>">
									<?php esc_html_e( 'Download PDF button', 'woocommerce-product-builder' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui checkbox toggle">
                                    <input type="checkbox" id="<?php echo esc_attr( self::set_option_field( 'download_pdf' ) ) ?>"
                                           name="<?php echo esc_attr( self::set_option_field( 'download_pdf' ) ) ?>"
                                           value="1" <?php checked( self::get_option_field( 'download_pdf' ), 1 ) ?>>
                                </div>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo esc_attr( self::set_option_field( 'show_short_desc' ) ) ?>">
									<?php esc_html_e( 'Show short description', 'woocommerce-product-builder' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui checkbox toggle">
                                    <input type="checkbox" id="<?php echo esc_attr( self::set_option_field( 'show_short_desc' ) ) ?>"
                                           name="<?php echo esc_attr( self::set_option_field( 'show_short_desc' ) ) ?>"
                                           value="1" <?php checked( self::get_option_field( 'show_short_desc' ), 1 ) ?>>
                                </div>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo esc_attr( self::set_option_field( 'layout_header' ) ) ?>">
									<?php esc_html_e( 'Header of layout', 'woocommerce-product-builder' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="field">
									<?php
									$default_content = "<table style=\"border: none; border-collapse: collapse; line-height: 1.5;\" width=\"100%\">
                                                        <tbody>
                                                        <tr>
                                                        <td style=\"padding: 5px; border: none; vertical-align: top;\" width=\"120\">Logo</td>
                                                        <td style=\"vertical-align: top; border: none; padding: 10px;\"><strong style=\"font-size: 20px;\">{site_title}
                                                        </strong><strong>Email: </strong>{admin_email}
                                                        <strong>Address:</strong> {store_address}
                                                        <strong>Website:</strong> {site_url}</td>
                                                        </tr>
                                                        </tbody>
                                                        </table>
                                                        <h1 style=\"text-align: center;\"><strong>Product builder</strong></h1>
                                                        &nbsp;";

									$content   = self::get_option_field( 'layout_header', $default_content );
									$editor_id = 'layout_header';

									wp_editor( $content, $editor_id );
									?>
                                </div>
                                <p class="description"><?php esc_html_e( 'Shortcode:', 'woocommerce-product-builder' ) ?></p>
                                <ul class="description" style="list-style: none">
                                    <li>
                                        <span>{admin_email}</span>
                                        - <?php esc_html_e( 'Your admin email.', 'woocommerce-product-builder' ) ?>
                                    </li>
                                    <li>
                                        <span>{store_address}</span>
                                        - <?php esc_html_e( 'Your store address', 'woocommerce-product-builder' ) ?>
                                    </li>
                                    <li>
                                        <span>{site_url}</span>
                                        - <?php esc_html_e( 'Your site url', 'woocommerce-product-builder' ) ?>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo esc_attr( self::set_option_field( 'layout_footer' ) ) ?>"><?php esc_html_e( 'Footer of layout', 'woocommerce-product-builder' ) ?></label>
                            </th>
                            <td>
                                <div class="field">
									<?php
									$default_content = "";
									$content         = self::get_option_field( 'layout_footer', $default_content );
									$editor_id       = 'layout_footer';

									wp_editor( $content, $editor_id );
									?>
                                </div>
                                <p class="description"><?php esc_html_e( 'Shortcode:', 'woocommerce-product-builder' ) ?></p>
                                <ul class="description" style="list-style: none">
                                    <li>
                                        <span>{admin_email}</span>
                                        - <?php esc_html_e( 'Your admin email.', 'woocommerce-product-builder' ) ?>
                                    </li>
                                    <li>
                                        <span>{store_address}</span>
                                        - <?php esc_html_e( 'Your store address', 'woocommerce-product-builder' ) ?>
                                    </li>
                                    <li>
                                        <span>{site_url}</span>
                                        - <?php esc_html_e( 'Your site url', 'woocommerce-product-builder' ) ?>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!--Update-->
                <div class="vi-ui bottom attached tab segment " data-tab="update">
                    <table class="form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="auto-update-key"><?php esc_html_e( 'Auto Update Key', 'woocommerce-product-builder' ) ?></label>
                            </th>
                            <td>
                                <div class="fields">
                                    <div class="ten wide field">
                                        <input type="text" name="<?php echo esc_attr( self::set_option_field( 'key' ) ) ?>"
                                               id="auto-update-key"
                                               class="villatheme-autoupdate-key-field"
                                               value="<?php echo esc_attr( self::get_option_field( 'key' ) ) ?>">
                                    </div>
                                    <div class="six wide field">
                                        <span class="vi-ui button green villatheme-get-key-button"
                                              data-href="https://api.envato.com/authorization?response_type=code&client_id=villatheme-download-keys-6wzzaeue&redirect_uri=https://villatheme.com/update-key"
                                              data-id="19934326"><?php echo esc_html__( 'Get Key', 'woocommerce-product-builder' ) ?></span>
                                    </div>
                                </div>
								<?php do_action( 'woocommerce-product-builder_key' ) ?>
                                <p class="description"><?php printf( '%1$s <a target="_blank" href="https://villatheme.com/my-download">Villatheme</a>. %2$s <a target="_blank" href="https://villatheme.com/knowledge-base/how-to-use-auto-update-feature/">%3$s</a>',
										esc_html__( 'Please fill your key what you get from', 'woocommerce-product-builder' ),
										esc_html__( 'You can automatically update WooCommerce Product Builder plugin. See guide', 'woocommerce-product-builder' ),
										esc_html__( 'here', 'woocommerce-product-builder' ) ) ?></p>

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <p>
                    <button class="vi-ui button primary woopb-button-save">
						<?php esc_html_e( 'Save', 'woocommerce-product-builder' ); ?>
                    </button>
                    <button class="vi-ui button woopb-button-save"
                            name="<?php echo esc_attr( self::set_option_field( 'check_key' ) ) ?>">
						<?php esc_html_e( 'Save & Check Key', 'woocommerce-product-builder' ); ?>
                    </button>
                </p>

            </form>
			<?php do_action( 'villatheme_support_woocommerce-product-builder' ) ?>
        </div>
	<?php }

	function setting_menu() {
		add_submenu_page(
			'edit.php?post_type=woo_product_builder',
			esc_html__( 'WooCommerce Product Builder Setting', 'woocommerce-product-builder' ),
			esc_html__( 'Settings', 'woocommerce-product-builder' ),
			'manage_options',
			'woocommerce-product-builder-setting',
			array( $this, 'page_callback' )
		);
	}
}