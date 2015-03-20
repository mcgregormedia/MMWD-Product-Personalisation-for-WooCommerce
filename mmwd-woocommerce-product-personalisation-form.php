<?php

class mmwd_product_personalisation_form {

    public function __construct() {
		
        add_action( 'woocommerce_before_add_to_cart_button', array( &$this, 'mmwd_wc_before_add_to_cart_button' ) );
        add_filter( 'woocommerce_add_cart_item_data', array( &$this, 'mmwd_wc_add_cart_item_data' ), 10, 2 );
        add_filter( 'woocommerce_get_cart_item_from_session', array( &$this, 'mmwd_wc_get_cart_item_from_session' ), 10, 2 );
        add_filter( 'woocommerce_get_item_data', array( &$this, 'mmwd_wc_get_item_data' ), 10, 2 );
        add_action( 'woocommerce_add_order_item_meta', array( &$this, 'mmwd_wc_add_order_item_meta' ), 10, 2 );
		
    }

    /* Add custom fields to product */
    public function mmwd_wc_before_add_to_cart_button() {
		
        global $post;
        $mmwd_wc_pp_settings = get_post_meta( $post->ID, '_mmwd_wc_pp_settings', true );
		
		if ( isset( $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) && $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) {
			$_mmwd_personalisation_max_char = ( $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ) ? ' Maximum number of characters: ' . esc_html( $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ) . '.' : '';
			$_mmwd_personalisation_max_char_attr = ( $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ) ? 'maxlength="' . esc_html( $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ) . '"' : '';
			echo '<div class="mmwd_wc_product_meta">
                    <p>Personalisation<br>
                    <input id="_mmwd_personalisation_text" name="_mmwd_personalisation_text" type="text" ' . $_mmwd_personalisation_max_char_attr . '><br>
					<em>Ensure this text is correct as the seller will personalise your item exactly as it is entered here.' . $_mmwd_personalisation_max_char . '</em></p>
                </div>';
        }
		
		if ( isset( $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) && $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) {
			echo '<div class="mmwd_wc_product_meta">
                    <p><input id="_mmwd_gift_wrap_selected" name="_mmwd_gift_wrap_selected" type="checkbox"> Gift Wrap?</p>
                </div>';
        }
		
    }

    /* Filters for cart actions */

    public function mmwd_wc_add_cart_item_data( $cart_item_meta, $product_id ) {
		
        global $woocommerce;
        $mmwd_wc_pp_settings = get_post_meta( $product_id, '_mmwd_wc_pp_settings', true );
		
        if ( ( isset( $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) && $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) || ( isset( $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) && $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) ) {
            $cart_item_meta['_mmwd_wc_pp_settings'] = $mmwd_wc_pp_settings;
            
			$cart_item_meta['_mmwd_wc_data']['_mmwd_personalisation_text'] = (  isset(  $_POST['_mmwd_personalisation_text']  ) && $_POST['_mmwd_personalisation_text'] != '' ) ? $_POST['_mmwd_personalisation_text'] : '';
			
			$cart_item_meta['_mmwd_wc_data']['_mmwd_gift_wrap_selected'] = (  isset(  $_POST['_mmwd_gift_wrap_selected']  ) && $_POST['_mmwd_gift_wrap_selected'] != '' ) ? $_POST['_mmwd_gift_wrap_selected'] : ''; 

        }

        return $cart_item_meta;
		
    }
	
	/* Get the data from the session */
    public function mmwd_wc_get_cart_item_from_session( $cart_item, $values ) {

        // Add the form options meta to the cart item in case you want to do special stuff on the check out page.
        if ( isset( $values['_mmwd_wc_pp_settings'] ) ) {
            $cart_item['_mmwd_wc_pp_settings'] = $values['_mmwd_wc_pp_settings'];
        }

        // Add the product meta data to the cart
        if ( isset( $values['_mmwd_wc_data'] ) ) {
            $cart_item['_mmwd_wc_data'] = $values['_mmwd_wc_data'];
        }

        return $cart_item;
		
    }

    public function mmwd_wc_get_item_data( $other_data, $cart_item ) {
		
        $mmwd_wc_pp_settings = $cart_item['_mmwd_wc_pp_settings'];
		
        if ( ( isset( $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) && $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) || ( isset( $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) && $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) ) {
            if ( isset( $cart_item['_mmwd_wc_data'] ) ) {

                $data = $cart_item['_mmwd_wc_data'];
				$gift_wrap_selected = $data['_mmwd_gift_wrap_selected'] ? 'Yes' : 'No';

                // Add custom data to product data
                $other_data[] = array( 'name' => 'Personalisation', 'value' => $data['_mmwd_personalisation_text'] );
				$other_data[] = array( 'name' => 'Gift wrap', 'value' => $gift_wrap_selected );
            }
        }

        return $other_data;
		
    }
	
	public function mmwd_wc_add_order_item_meta( $order_item_id, $cart_item ){
		
		$mmwd_wc_pp_settings = $cart_item['_mmwd_wc_pp_settings'];
		$data = $cart_item['_mmwd_wc_data'];
		
		if ( isset( $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) && $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) {
			
			if ( ! empty( $data['_mmwd_personalisation_text'] ) ){
				woocommerce_add_order_item_meta(
					$order_item_id,
					__( 'Personalisation', 'mmwd-wc-product-personalisation' ),
					__( $data['_mmwd_personalisation_text'], 'mmwd-wc-product-personalisation' )
				); 
			} 
		}
		
		if ( isset( $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) && $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) {
			if ( ! empty( $data['_mmwd_gift_wrap_selected'] ) ){
				woocommerce_add_order_item_meta(
					$order_item_id,
					__( 'Gift wrap', 'mmwd-wc-product-personalisation' ),
					__( 'Yes', 'mmwd-wc-product-personalisation' )
				); 
			}
		}
		
	}

}