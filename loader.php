<?php


/*
 Plugin Name: BuddyForms Easy Digital Downloads
 Plugin URI: http://buddyforms.com/downloads/buddyforms-easy-digital-downloads/
 Description: BuddyForms Easy Digital Downloads
 Version: 0.1
 Author: ThemeKraft
 Author URI: https://themekraft.com/buddyforms/
 License: GPLv2 or later
 Network: false

 *****************************************************************************
 *
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ****************************************************************************
 */
/*
 * Add EDD  form elementrs in the form elements select box
 */
function buddyforms_edd_elements_to_select( $elements_select_options ) {
	global $post;

	if ( $post->post_type != 'buddyforms' ) {
		return;
	}
	$elements_select_options['edd']['label'] = 'Easy Digital Downloads';
	$elements_select_options['edd']['class'] = 'bf_show_if_f_type_post';
	$elements_select_options['edd']['fields']['edd-prices'] = array(
		'label'     => __( 'Download Prices', 'buddyforms' ),
		'unique'    => 'unique'
	);

	$elements_select_options['edd']['fields']['edd-files'] = array(
		'label'     => __( 'Download Files', 'buddyforms' ),
		'unique'    => 'unique'
	);

	return $elements_select_options;
}

add_filter( 'buddyforms_add_form_element_select_option', 'buddyforms_edd_elements_to_select', 1, 2 );


/*
 * Create the new ACF Form Builder Form Elements
 *
 */
function buddyforms_edd_form_builder_form_elements( $form_fields, $form_slug, $field_type, $field_id ) {
	global $field_position, $buddyforms;

	switch ( $field_type ) {
		case 'edd-prices':

			if( ! function_exists('edd_render_download_meta_box' ) ){
				break;
			}

			unset( $form_fields );


			$form_fields['general']['name']  = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][name]", 'edd-prices' );
			$form_fields['advanced']['slug'] = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][slug]", 'edd-prices' );
			$form_fields['general']['type']  = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][type]", $field_type );
			$form_fields['general']['order'] = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][order]", $field_position, array( 'id' => 'buddyforms/' . $form_slug . '/form_fields/' . $field_id . '/order' ) );
			break;
		case 'edd-files':

			if( ! function_exists('edd_render_download_meta_box' ) ){
				break;
			}

			unset( $form_fields );


			$form_fields['general']['name'] = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][name]", 'edd-files' );
			$form_fields['advanced']['slug']  = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][slug]", 'edd-files' );
			$form_fields['general']['type']  = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][type]", $field_type );
			$form_fields['general']['order'] = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][order]", $field_position, array( 'id' => 'buddyforms/' . $form_slug . '/form_fields/' . $field_id . '/order' ) );
			break;

	}

	return $form_fields;
}
add_filter( 'buddyforms_form_element_add_field', 'buddyforms_edd_form_builder_form_elements', 1, 5 );

/*
 * Display the new ACF Fields in the frontend form
 *
 */
function buddyforms_edd_frontend_form_elements( $form, $form_args ) {
	global $buddyforms, $nonce;

	extract( $form_args );

	$post_type = $buddyforms[ $form_slug ]['post_type'];

	if ( ! $post_type ) {
		return $form;
	}

	if ( ! isset( $customfield['type'] ) ) {
		return $form;
	}

	switch ( $customfield['type'] ) {
		case 'edd-prices':
			$post_id = $post_id == 0 ? 'new_post' : $post_id;

			require_once EDD_PLUGIN_DIR . 'includes/admin/downloads/metabox.php';

			$tmp = '<div id="poststuff">';
			$tmp .= '<div class="bf_inputs">';

			ob_start();
			edd_render_download_meta_box();
			$tmp .= ob_get_clean();

			$tmp .= '</div> ';
			$tmp .= '</div>';

			$form->addElement( new Element_HTML( $tmp ) );

			break;
		case 'edd-files':

			$post_id = $post_id == 0 ? 'new_post' : $post_id;

			require_once EDD_PLUGIN_DIR . 'includes/admin/downloads/metabox.php';

			$tmp = '<div id="poststuff">';
			$tmp .= '<div class="bf_inputs">';

			ob_start();
			edd_render_files_meta_box();
			$tmp .= ob_get_clean();

			$tmp .= '</div> ';
			$tmp .= '</div>';

			$form->addElement( new Element_HTML( $tmp ) );
			break;
	}

	return $form;
}

add_filter( 'buddyforms_create_edit_form_display_element', 'buddyforms_edd_frontend_form_elements', 1, 2 );

/*
 * Save ACF Fields
 *
 */
function buddyforms_edd_update_post_meta( $customfield, $post_id ) {
	global $post;

	if ( $customfield['type'] == 'edd-files' ) {
		buddyforms_edd_download_meta_box_save( $post_id, $post );
	}
	if ( $customfield['type'] == 'edd-prices' ) {
		buddyforms_edd_download_meta_box_save( $post_id, $post );
	}
}
add_action( 'buddyforms_update_post_meta', 'buddyforms_edd_update_post_meta', 10, 2 );


add_action('wp_enqueue_scripts', 'buddyforms_edd_load_admin_scripts', 100, 1);

function buddyforms_edd_load_admin_scripts( $hook ) {

		global $post;

		$js_dir  = EDD_PLUGIN_URL . 'assets/js/';
		$css_dir = EDD_PLUGIN_URL . 'assets/css/';

		// Use minified libraries if SCRIPT_DEBUG is turned off
		$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// These have to be global
		wp_register_style( 'jquery-chosen', $css_dir . 'chosen' . $suffix . '.css', array(), EDD_VERSION );
		wp_enqueue_style( 'jquery-chosen' );

		wp_register_script( 'jquery-chosen', $js_dir . 'chosen.jquery' . $suffix . '.js', array( 'jquery' ), EDD_VERSION );
		wp_enqueue_script( 'jquery-chosen' );

		wp_enqueue_script( 'jquery-form' );

		$admin_deps = array();

//		$admin_deps = array( 'jquery', 'jquery-form', 'inline-edit-post' );

		wp_register_script( 'edd-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', $admin_deps, EDD_VERSION, false );

		wp_enqueue_script( 'edd-admin-scripts' );

		wp_localize_script( 'edd-admin-scripts', 'edd_vars', array(
			'post_id'                     => isset( $post->ID ) ? $post->ID : null,
			'edd_version'                 => EDD_VERSION,
			'add_new_download'            => __( 'Add New Download', 'easy-digital-downloads' ),
			'use_this_file'               => __( 'Use This File', 'easy-digital-downloads' ),
			'quick_edit_warning'          => __( 'Sorry, not available for variable priced products.', 'easy-digital-downloads' ),
			'delete_payment'              => __( 'Are you sure you wish to delete this payment?', 'easy-digital-downloads' ),
			'delete_payment_note'         => __( 'Are you sure you wish to delete this note?', 'easy-digital-downloads' ),
			'delete_tax_rate'             => __( 'Are you sure you wish to delete this tax rate?', 'easy-digital-downloads' ),
			'revoke_api_key'              => __( 'Are you sure you wish to revoke this API key?', 'easy-digital-downloads' ),
			'regenerate_api_key'          => __( 'Are you sure you wish to regenerate this API key?', 'easy-digital-downloads' ),
			'resend_receipt'              => __( 'Are you sure you wish to resend the purchase receipt?', 'easy-digital-downloads' ),
			'copy_download_link_text'     => __( 'Copy these links to your clipboard and give them to your customer', 'easy-digital-downloads' ),
			'delete_payment_download'     => sprintf( __( 'Are you sure you wish to delete this %s?', 'easy-digital-downloads' ), edd_get_label_singular() ),
			'one_price_min'               => __( 'You must have at least one price', 'easy-digital-downloads' ),
			'one_field_min'               => __( 'You must have at least one field', 'easy-digital-downloads' ),
			'one_download_min'            => __( 'Payments must contain at least one item', 'easy-digital-downloads' ),
			'one_option'                  => sprintf( __( 'Choose a %s', 'easy-digital-downloads' ), edd_get_label_singular() ),
			'one_or_more_option'          => sprintf( __( 'Choose one or more %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
			'numeric_item_price'          => __( 'Item price must be numeric', 'easy-digital-downloads' ),
			'numeric_item_tax'            => __( 'Item tax must be numeric', 'easy-digital-downloads' ),
			'numeric_quantity'            => __( 'Quantity must be numeric', 'easy-digital-downloads' ),
			'currency'                    => edd_get_currency(),
			'currency_sign'               => edd_currency_filter( '' ),
			'currency_pos'                => edd_get_option( 'currency_position', 'before' ),
			'currency_decimals'           => edd_currency_decimal_filter(),
			'decimal_separator'           => edd_get_option( 'decimal_separator', '.' ),
			'thousands_separator'         => edd_get_option( 'thousands_separator', ',' ),
			'new_media_ui'                => apply_filters( 'edd_use_35_media_ui', 1 ),
			'remove_text'                 => __( 'Remove', 'easy-digital-downloads' ),
			'type_to_search'              => sprintf( __( 'Type to search %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
			'quantities_enabled'          => edd_item_quantities_enabled(),
			'batch_export_no_class'       => __( 'You must choose a method.', 'easy-digital-downloads' ),
			'batch_export_no_reqs'        => __( 'Required fields not completed.', 'easy-digital-downloads' ),
			'reset_stats_warn'            => __( 'Are you sure you want to reset your store? This process is <strong><em>not reversible</em></strong>. Please be sure you have a recent backup.', 'easy-digital-downloads' ),
			'unsupported_browser'         => __( 'We are sorry but your browser is not compatible with this kind of file upload. Please upgrade your browser.', 'easy-digital-downloads' ),
			'show_advanced_settings'      => __( 'Show advanced settings', 'easy-digital-downloads' ),
			'hide_advanced_settings'      => __( 'Hide advanced settings', 'easy-digital-downloads' ),
		));

		/*
		 * This bit of JavaScript is to facilitate #2704, in order to not break backwards compatibility with the old Variable Price Rows
		 * while we transition to an entire new markup. They should not be relied on for long-term usage.
		 *
		 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/2704
		 */
		wp_register_script( 'edd-admin-scripts-compatibility', $js_dir . 'admin-backwards-compatibility' . $suffix . '.js', array( 'jquery', 'edd-admin-scripts' ), EDD_VERSION );
		wp_localize_script( 'edd-admin-scripts-compatibility', 'edd_backcompat_vars', array(
			'purchase_limit_settings'     => __( 'Purchase Limit Settings', 'easy-digital-downloads' ),
			'simple_shipping_settings'    => __( 'Simple Shipping Settings', 'easy-digital-downloads' ),
			'software_licensing_settings' => __( 'Software Licensing Settings', 'easy-digital-downloads' ),
			'recurring_payments_settings' => __( 'Recurring Payments Settings', 'easy-digital-downloads' ),
		) );

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_register_style( 'colorbox', $css_dir . 'colorbox' . $suffix . '.css', array(), '1.3.20' );
		wp_enqueue_style( 'colorbox' );

		wp_register_script( 'colorbox', $js_dir . 'jquery.colorbox-min.js', array( 'jquery' ), '1.3.20' );
		wp_enqueue_script( 'colorbox' );

		//call for media manager
		wp_enqueue_media();

		wp_register_script( 'jquery-flot', $js_dir . 'jquery.flot' . $suffix . '.js' );
		wp_enqueue_script( 'jquery-flot' );

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tooltip' );

		$ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
		wp_register_style( 'jquery-ui-css', $css_dir . 'jquery-ui-' . $ui_style . $suffix . '.css' );
		wp_enqueue_style( 'jquery-ui-css' );

		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		wp_register_style( 'edd-admin', $css_dir . 'edd-admin' . $suffix . '.css', array(), EDD_VERSION );
		wp_enqueue_style( 'edd-admin' );
}

/**
 * Save post meta when the save_post action is called
 *
 * @since 1.0
 * @param int $post_id Download (Post) ID
 * @global array $post All the data of the the current post
 * @return void
 */
function buddyforms_edd_download_meta_box_save( $post_id, $post ) {

//	if ( ! isset( $_POST['edd_download_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['edd_download_meta_box_nonce'] ) ) {
//		return;
//	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	// The default fields that get saved
	$fields = edd_download_metabox_fields();

	foreach ( $fields as $field ) {

		// Accept blank or "0"
		if ( '_edd_download_limit' == $field ) {
			if ( ! empty( $_POST[ $field ] ) || ( isset( $_POST[ $field ] ) && strlen( $_POST[ $field ] ) === 0 ) || ( isset( $_POST[ $field ] ) && "0" === $_POST[ $field ] ) ) {

				$global_limit = edd_get_option( 'file_download_limit' );
				$new_limit    = apply_filters( 'edd_metabox_save_' . $field, $_POST[ $field ] );

				// Only update the new limit if it is not the same as the global limit
				if( $global_limit == $new_limit ) {

					delete_post_meta( $post_id, '_edd_download_limit' );

				} else {

					update_post_meta( $post_id, '_edd_download_limit', $new_limit );

				}
			}

		} elseif ( '_edd_default_price_id' == $field && edd_has_variable_prices( $post_id ) ) {

			if ( isset( $_POST[ $field ] ) ) {
				$new_default_price_id = ( ! empty( $_POST[ $field ] ) && is_numeric( $_POST[ $field ] ) ) || ( 0 === (int) $_POST[ $field ] ) ? (int) $_POST[ $field ] : 1;
			} else {
				$new_default_price_id = 1;
			}

			update_post_meta( $post_id, $field, $new_default_price_id );

		} else {

			if ( ! empty( $_POST[ $field ] ) ) {
				$new = apply_filters( 'edd_metabox_save_' . $field, $_POST[ $field ] );
				update_post_meta( $post_id, $field, $new );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}

	}

	if ( edd_has_variable_prices( $post_id ) ) {
		$lowest = edd_get_lowest_price_option( $post_id );
		update_post_meta( $post_id, 'edd_price', $lowest );
	}

	do_action( 'edd_save_download', $post_id, $post );
}