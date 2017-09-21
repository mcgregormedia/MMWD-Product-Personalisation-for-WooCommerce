<?php
/**
 * Plugin Name: MMWD Product Personalisation for WooCommerce
 * Description: Adds form fields on the frontend product page for personalisation and/or gift wrap. Adds this data as order item meta data.
 * Version: 1.1.2
 * Author: McGregor Media Web Design
 * Author URI: http://mcgregormedia.co.uk
 * Text Domain: mmwd-wc-product-personalisation
 * License: GPL2
 */
 
/*
 * MMWD Product Personalisation for WooCommerce is based on a plugin by Craig Martin at http://www.xatik.com/2013/02/06/add-custom-form-woocommerce-product/ and updated for WooCommerce 2+ by Graylien at http://graylien.tumblr.com/post/68589758281/woocommerce-2-0-new-plugin-hooks.
 */




if ( ! defined( 'ABSPATH' ) ) {
	
	exit; // Came directly here? Vamoose. Go on, scram.
	
}




/**
 * Loads translation files
 * 
 * @since 1.2.0					Added function
 */

function pofwc_load_textdomain() {
	
	load_plugin_textdomain( 'pofwc', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
	
}
add_action( 'plugins_loaded', 'pofwc_load_textdomain' );




if (  in_array(  'woocommerce/woocommerce.php', apply_filters(  'active_plugins', get_option(  'active_plugins'  )  )  )  ) { // Check if WooCommerce is active




    class MMWD_Product_Personalisation {
		
		
		
        /**
         * Constructor
		 *
		 * @since 1.0.0
         */
		
        public function __construct(  ) {

            include_once(  'mmwd-woocommerce-product-personalisation-form.php' );
            $mmwd_product_personalisation_form = new mmwd_product_personalisation_form(  );

            add_action( 'woocommerce_product_options_general_product_data', array( &$this, 'mmwd_wc_add_to_product_general_settings' ) );
            add_action( 'woocommerce_process_product_meta', array( &$this, 'mmwd_wc_process_meta_box' ), 1, 2 );
        }
		
		
		
		
        /**
         * Adds options to Product Data/General tab
         * 
         * @param array $post  	The post object
         * 
         * @return string		The formatted html
		 *
		 * @since 1.0.0			Added function
		 * @since 1.1.0			Added support for multiple lines of personalisation
         */
		
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
				woocommerce_wp_select( array( 
					'id'      			=> '_mmwd_display_personalisation_number_of_fields', 
					'label'   			=> __( 'Number of rows', 'mmwd-wc-product-personalisation' ), 
					'description'		=> __( 'How many rows can the customer personalise? Requires Personalisation to be ticked.', 'mmwd-wc-product-personalisation' ),
					'desc_tip'			=> true,
					'options' 			=> array(
						'1'		=> __( '1', 'mmwd-wc-product-personalisation' ),
						'2'		=> __( '2', 'mmwd-wc-product-personalisation' ),
						'3'		=> __( '3', 'mmwd-wc-product-personalisation' ),
						'4'		=> __( '4', 'mmwd-wc-product-personalisation' ),
						'5'		=> __( '5', 'mmwd-wc-product-personalisation' ),
						'6'		=> __( '6', 'mmwd-wc-product-personalisation' ),
						'7'		=> __( '7', 'mmwd-wc-product-personalisation' ),
						'8'		=> __( '8', 'mmwd-wc-product-personalisation' ),
						'9'		=> __( '9', 'mmwd-wc-product-personalisation' ),
						'10'	=> __( '10', 'mmwd-wc-product-personalisation' )
					),
					'value' 		=> $mmwd_wc_pp_settings['_mmwd_display_personalisation_number_of_fields'],
					)
				);
				woocommerce_wp_text_input( array(
					'id' 				=> '_mmwd_personalisation_max_char',
					'label' 			=> __( 'Max characters per line', 'mmwd-wc-product-personalisation' ),
					'description' 		=> __( 'The maximum number of characters per line a customer can use for personalisation.', 'mmwd-wc-product-personalisation' ),
					'desc_tip'			=> true,
					'value' 			=> isset( $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ) && $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ? esc_html( $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ) : '',
					'type' 				=> 'number',
					)
				);
				woocommerce_wp_checkbox( array( 
					'id' 				=> '_mmwd_display_gift_wrap',
					'label' 			=> __( 'Gift wrap', 'mmwd-wc-product-personalisation' ),
					'description'		=> __( 'Tick this box to offer a gift wrapping service on the product page.', 'mmwd-wc-product-personalisation' ),
					'desc_tip'			=> true,
					'value' 			=> isset( $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) && $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ? '1' : '',
					'cbvalue' 			=> '1',
					)
				);
				?>
			</div>
            <?php
        }
		
		
		
		
        /**
         * Updates post meta on admin page update
         * 
         * @param int $post_id 		The post ID
         * @param array $post 		The post object
		 *
		 * @since 1.0.0				Added function
		 * @since 1.1.0				Added support for multiple lines of personalisation
         */
		
        static function mmwd_wc_process_meta_box( $post_id, $post ) {
            global $woocommerce_errors;

            if ( isset( $_POST['_mmwd_display_personalisation'] ) || isset( $_POST['_mmwd_display_gift_wrap'] ) ) {
                $mmwd_wc_pp_settings = array( 
                    '_mmwd_display_personalisation' => isset( $_POST['_mmwd_display_personalisation'] ) ? true : false,
                    '_mmwd_display_personalisation_number_of_fields' => $_POST['_mmwd_display_personalisation_number_of_fields'],
					'_mmwd_personalisation_max_char' => isset( $_POST['_mmwd_personalisation_max_char'] ) && is_numeric( $_POST['_mmwd_personalisation_max_char'] ) ? sanitize_text_field( $_POST['_mmwd_personalisation_max_char'] ) : '',
					'_mmwd_display_gift_wrap' => isset( $_POST['_mmwd_display_gift_wrap'] ) ? true : false,
                 );
                update_post_meta( $post_id, '_mmwd_wc_pp_settings', $mmwd_wc_pp_settings );
            } else {
                delete_post_meta( $post_id, '_mmwd_wc_pp_settings' );
            }
        }
    }

    $mmwd_product_personalisation = new MMWD_Product_Personalisation();
	
}