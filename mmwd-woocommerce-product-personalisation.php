<?php
/**
 * Plugin Name: MMWD WooCommerce Product Personalisation
 * Plugin URI: http://mcgregormedia.co.uk/projects/mmwd-woocommerce-product-personalisation/
 * Description: Adds form fields on the frontend product page for personalisation and gift wrap. Adds this data as order item meta data.
 * Version: 1.0.2
 * Author: McGregor Media Web Design
 * Author URI: http://mcgregormedia.co.uk
 * Text Domain: mmwd-wc-product-personalisation
 * License: GPL2
 */
 
/*
 * MMWD WooCommerce Product Personalisation is based on a plugin by Craig Martin at http://www.xatik.com/2013/02/06/add-custom-form-woocommerce-product/ and updated for WooCommerce 2.x by Graylien at http://graylien.tumblr.com/post/68589758281/woocommerce-2-0-new-plugin-hooks.
 */

if (  in_array(  'woocommerce/woocommerce.php', apply_filters(  'active_plugins', get_option(  'active_plugins'  )  )  )  ) { // Check if WooCommerce is active

    class mmwd_product_personalisation {
		
        public function __construct(  ) {

            include_once(  'mmwd-woocommerce-product-personalisation-form.php' );
            $mmwd_product_personalisation_form = new mmwd_product_personalisation_form(  );

            add_action( 'woocommerce_product_options_general_product_data', array( &$this, 'mmwd_wc_add_to_product_general_settings' ) );
            add_action( 'woocommerce_process_product_meta', array( &$this, 'mmwd_wc_process_meta_box' ), 1, 2 );
        }

        /* Add options to Product Data/General tab */
        function mmwd_wc_add_to_product_general_settings( $post ) {
            ?>
			
			<div class="options_group">
				<?php
				
				global $post;
				$mmwd_wc_pp_settings = get_post_meta( $post->ID, '_mmwd_wc_pp_settings', true );
				
				woocommerce_wp_checkbox( array( 
					'id' 				=> '_mmwd_display_personalisation',
					'label' 			=> __( 'Personalisation', 'mmwd-wc-product-personalisation' ),
					'description'		=> __( 'Tick this box to show a personalisation text box on the product page.', 'mmwd-wc-product-personalisation' ),
					'desc_tip'			=> true,
					'value' 			=> isset( $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) && $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ? '1' : '',
					'cbvalue' 			=> '1',
					)
				);
				woocommerce_wp_text_input( array(
					'id' 				=> '_mmwd_personalisation_max_char',
					'label' 			=> __( 'Max characters', 'mmwd-wc-product-personalisation' ),
					'description' 		=> __( 'The maximum number of characters a customer can use for personalisation.', 'mmwd-wc-product-personalisation' ),
					'desc_tip'			=> true,
					'value' 			=> isset( $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ) && $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ? esc_html( $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ) : '',
					'type' 				=> 'number',
					)
				);
				/* woocommerce_wp_text_input( array(
					'id' 				=> '_mmwd_personalisation_price',
					'label' 			=> __( 'Personalisation price (' . get_woocommerce_currency_symbol()  . ')', 'mmwd-wc-product-personalisation' ),
					'desc_tip'			=> true,
					'description' 		=> __( 'Cost of personalisation. This will increase the product price.', 'mmwd-wc-product-personalisation' ),
					'desc_tip'			=> true,
					'value' 			=> esc_html( $mmwd_wc_pp_settings['_mmwd_personalisation_price'] ),
					'type' 				=> 'text',
					)
				);  */
				woocommerce_wp_checkbox( array( 
					'id' 				=> '_mmwd_display_gift_wrap',
					'label' 			=> __( 'Gift wrap', 'mmwd-wc-product-personalisation' ),
					'description'		=> __( 'Tick this box to offer a gift wrapping service on the product page.', 'mmwd-wc-product-personalisation' ),
					'desc_tip'			=> true,
					'value' 			=> isset( $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) && $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ? '1' : '',
					'cbvalue' 			=> '1',
					)
				);
				/* woocommerce_wp_text_input( array(
					'id' 				=> '_mmwd_gift_wrap_price',
					'label' 			=> __( 'Gift wrap price (' . get_woocommerce_currency_symbol()  . ')', 'mmwd-wc-product-personalisation' ),
					'description' 		=> __( 'Cost of gift wrapping. This will increase the product price.', 'mmwd-wc-product-personalisation' ),
					'desc_tip'			=> true,
					'value' 			=> esc_html( $mmwd_wc_pp_settings['_mmwd_gift_wrap_price'] ),
					'type' 				=> 'text',
					)
				);  */
				?>
			</div>
            <?php
        }

        /* Update post meta on page update */
        function mmwd_wc_process_meta_box( $post_id, $post ) {
            global $woocommerce_errors;

            if ( isset( $_POST['_mmwd_display_personalisation'] ) || isset( $_POST['_mmwd_display_gift_wrap'] ) || isset( $_POST['_mmwd_gift_wrap_price'] ) ) {
                $mmwd_wc_pp_settings = array( 
                    '_mmwd_display_personalisation' => isset( $_POST['_mmwd_display_personalisation'] ) ? true : false,
					'_mmwd_personalisation_max_char' => isset( $_POST['_mmwd_personalisation_max_char'] ) && is_numeric( $_POST['_mmwd_personalisation_max_char'] ) ? sanitize_text_field( $_POST['_mmwd_personalisation_max_char'] ) : '',
					/* '_mmwd_personalisation_price' => isset( $_POST['_mmwd_personalisation_price'] ) && is_numeric( $_POST['_mmwd_personalisation_price'] ) ? sanitize_text_field( $_POST['_mmwd_personalisation_price'] ) : '', */
					'_mmwd_display_gift_wrap' => isset( $_POST['_mmwd_display_gift_wrap'] ) ? true : false,
					/* '_mmwd_gift_wrap_price' => isset( $_POST['_mmwd_gift_wrap_price'] ) && is_numeric( $_POST['_mmwd_gift_wrap_price'] ) ? sanitize_text_field( $_POST['_mmwd_gift_wrap_price'] ) : '', */
                 );
                update_post_meta( $post_id, '_mmwd_wc_pp_settings', $mmwd_wc_pp_settings );
            } else {
                delete_post_meta( $post_id, '_mmwd_wc_pp_settings' );
            }
        }
    }

    $mmwd_product_personalisation = new mmwd_product_personalisation();
	
}