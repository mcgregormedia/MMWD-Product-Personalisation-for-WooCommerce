<?php

class mmwd_product_personalisation_form {



    /**
     * Constructor
     * 
	 * @since 1.0.0
     */
	
    public function __construct() {
		
        add_action( 'woocommerce_before_add_to_cart_button', array( &$this, 'mmwd_wc_before_add_to_cart_button' ) );
        add_filter( 'woocommerce_add_cart_item_data', array( &$this, 'mmwd_wc_add_cart_item_data' ), 10, 2 );
        add_filter( 'woocommerce_get_cart_item_from_session', array( &$this, 'mmwd_wc_get_cart_item_from_session' ), 10, 2 );
        add_filter( 'woocommerce_get_item_data', array( &$this, 'mmwd_wc_get_item_data' ), 10, 2 );
        add_action( 'woocommerce_add_order_item_meta', array( &$this, 'mmwd_wc_add_order_item_meta' ), 10, 2 );
		
    }

	
	
	
    /**
     * Adds custom fields to product 
     * 
     * @return string		The formatted html
	 *
	 * @since 1.0.0			Added function
	 * @since 1.1.0			Added support for multiple lines of personalisation
     */
    
    public function mmwd_wc_before_add_to_cart_button() {
		
        global $post;
        $mmwd_wc_pp_settings = get_post_meta( $post->ID, '_mmwd_wc_pp_settings', true );
		
		if ( isset( $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) && $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) {
			
			$_mmwd_display_personalisation_number_of_fields = $mmwd_wc_pp_settings['_mmwd_display_personalisation_number_of_fields'];
			$_mmwd_display_personalisation_number_of_fields_per_line_note = ( $_mmwd_display_personalisation_number_of_fields > 1 ) ? ' per line': '';
			$_mmwd_personalisation_max_char = ( isset( $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ) ) ? ' Maximum number of characters' . $_mmwd_display_personalisation_number_of_fields_per_line_note . ': ' . esc_html( $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ) . '.' : '';
			$_mmwd_personalisation_max_char_attr = ( $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ) ? 'maxlength="' . esc_html( $mmwd_wc_pp_settings['_mmwd_personalisation_max_char'] ) . '"' : '';
			
			?>			
			<div class="mmwd_wc_product_meta">
				<p>Personalisation<br>
				<input id="_mmwd_personalisation_text" name="_mmwd_personalisation_text" type="text" <?php echo $_mmwd_personalisation_max_char_attr; ?>><br>
				<?php
				if( $_mmwd_display_personalisation_number_of_fields > 1 ){
					
					$fields = 2;
					
					while ( $fields <= $_mmwd_display_personalisation_number_of_fields ){
						
						echo '<input id="_mmwd_personalisation_text_' . $fields . '" name="_mmwd_personalisation_text_' . $fields . '" type="text" ' . $_mmwd_personalisation_max_char_attr . '><br>';
						
						$fields++;
						
					}
					
				}
				?>
				<em>Ensure this text is correct as we will personalise your item exactly as it is entered here.<?php echo $_mmwd_personalisation_max_char; ?></em></p>
			</div>
			<?php
			
        }
		
		if ( isset( $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) && $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) {
			
			echo '<div class="mmwd_wc_product_meta">
                    <p><input id="_mmwd_gift_wrap_selected" name="_mmwd_gift_wrap_selected" type="checkbox"> Gift Wrap?</p>
                </div>';
				
        }
		
    }
	
	
	

    /**
     * Filters for cart actions 
     * 
     * @param array $cart_item_meta			The cart item meta
     * @param int $product_id  				The product ID
     * 
     * @return array $cart_item_meta 		The updated cart item meta
	 *
	 * @since 1.0.0							Added function
	 * @since 1.1.0							Added support for multiple lines of personalisation
     */
	
    public function mmwd_wc_add_cart_item_data( $cart_item_meta, $product_id ) {
		
        global $woocommerce;
        $mmwd_wc_pp_settings = get_post_meta( $product_id, '_mmwd_wc_pp_settings', true );
		$_mmwd_display_personalisation_number_of_fields = $mmwd_wc_pp_settings['_mmwd_display_personalisation_number_of_fields'];
		
        if ( isset( $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) && $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) {
			
            $cart_item_meta['_mmwd_wc_pp_settings'] = $mmwd_wc_pp_settings;
            
			$cart_item_meta['_mmwd_wc_data']['_mmwd_personalisation_text'] = (  isset(  $_POST['_mmwd_personalisation_text']  ) && $_POST['_mmwd_personalisation_text'] != '' ) ? $_POST['_mmwd_personalisation_text'] : '';
			
			if( $_mmwd_display_personalisation_number_of_fields > 1 ){
				
				$fields = 2;
				
				while ( $fields <= $_mmwd_display_personalisation_number_of_fields ){
					
					$cart_item_meta['_mmwd_wc_data']['_mmwd_personalisation_text_' . $fields] = (  isset(  $_POST['_mmwd_personalisation_text_' . $fields]  ) && $_POST['_mmwd_personalisation_text_' . $fields] != '' ) ? $_POST['_mmwd_personalisation_text_' . $fields] : '';
					
					$fields++;
					
				}
				
			}

        }
		
		if ( isset( $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) && $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ){
			
			$cart_item_meta['_mmwd_wc_data']['_mmwd_gift_wrap_selected'] = (  isset(  $_POST['_mmwd_gift_wrap_selected']  ) && $_POST['_mmwd_gift_wrap_selected'] != '' ) ? $_POST['_mmwd_gift_wrap_selected'] : ''; 
			
		}

        return $cart_item_meta;
		
    }
	
	
	
	
    /**
     * Gets the data from the session
     * 
     * @param array $cart_item			The cart item object
     * @param array $values  			The product meta data
     * 
     * @return array $cart_item			The updated cart item object
	 *
	 * @since 1.0.0
     */
	
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
	
	
	
    /**
     * Gets the item data for the cart
     * 
     * @param array $other_data 		The cart meta data	
     * @param array $cart_item			The cart item object
     * 
     * @return array $other_data 		The updated cart meta data	
	 *
	 * @since 1.0.0						Added function
	 * @since 1.1.0						Added support for multiple lines of personalisation
     */
	
    public function mmwd_wc_get_item_data( $other_data, $cart_item ) {
		
        $mmwd_wc_pp_settings = $cart_item['_mmwd_wc_pp_settings'];
		$_mmwd_display_personalisation_number_of_fields = $mmwd_wc_pp_settings['_mmwd_display_personalisation_number_of_fields'];
		
        if ( isset( $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) && $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) {
			
            if ( isset( $cart_item['_mmwd_wc_data'] ) ) {

                $data = $cart_item['_mmwd_wc_data'];
				$gift_wrap_selected = $data['_mmwd_gift_wrap_selected'] ? 'Yes' : 'No';

                // Add custom data to product data
                $other_data[] = array( 'name' => 'Personalisation line 1', 'value' => $data['_mmwd_personalisation_text'] );
				
				if( $_mmwd_display_personalisation_number_of_fields > 1 ){
					
					$fields = 2;
					
					while ( $fields <= $_mmwd_display_personalisation_number_of_fields ){
						
						$other_data[] = array( 'name' => 'Personalisation line ' . $fields, 'value' => $data['_mmwd_personalisation_text_' . $fields] );
						
						$fields++;
						
					}
					
				}			
				
			}
			
		}
		
		if ( isset( $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ) && $mmwd_wc_pp_settings['_mmwd_display_gift_wrap'] ){
			
			$other_data[] = array( 'name' => 'Gift wrap', 'value' => $gift_wrap_selected );
			
		}

        return $other_data;
		
    }
	
	
	
	
    /**
     * Adds the order item meta data
     * 
     * @param int $order_item_id 		The order item ID
     * @param array $cart_item			The cart item object
	 *
	 * @since 1.0.0						Added function
	 * @since 1.1.0						Added support for multiple lines of personalisation
     */
	
	public function mmwd_wc_add_order_item_meta( $order_item_id, $cart_item ){
		
		$mmwd_wc_pp_settings = $cart_item['_mmwd_wc_pp_settings'];
		$_mmwd_display_personalisation_number_of_fields = $mmwd_wc_pp_settings['_mmwd_display_personalisation_number_of_fields'];
		$data = $cart_item['_mmwd_wc_data'];
		
		if ( isset( $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) && $mmwd_wc_pp_settings['_mmwd_display_personalisation'] ) {
			
			if ( ! empty( $data['_mmwd_personalisation_text'] ) ){
				
				woocommerce_add_order_item_meta(
					$order_item_id,
					__( 'Personalisation line 1', 'mmwd-wc-product-personalisation' ),
					__( $data['_mmwd_personalisation_text'], 'mmwd-wc-product-personalisation' )
				); 
				
			}
			
			if( $_mmwd_display_personalisation_number_of_fields > 1 ){
				
				$fields = 2;
				while ( $fields <= $_mmwd_display_personalisation_number_of_fields ){
					
					if ( ! empty( $data['_mmwd_personalisation_text'] ) ){
						
						woocommerce_add_order_item_meta(
							$order_item_id,
							__( 'Personalisation line ' . $fields, 'mmwd-wc-product-personalisation' ),
							__( $data['_mmwd_personalisation_text_' . $fields], 'mmwd-wc-product-personalisation' )
						); 
					}
					
					$fields++;
					
				}
				
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