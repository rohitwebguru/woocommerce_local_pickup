jQuery.noConflict();
jQuery(document).ready(function($){
    function product_refill_inputs( $form ) {
        var inputs_values = pricing_item_meta_data;
        if ( false === $.isPlainObject( inputs_values ) || $.isEmptyObject( inputs_values ) ) {        
            return;
        }
        for ( var input_name in inputs_values ) {
            if ( false === inputs_values.hasOwnProperty( input_name ) ) {     
                continue;
            }
            $form.find( '.amount_needed[name="' + input_name + '"]:not(.fixed-value), input[name="' + input_name + '"].qty' ).val( inputs_values[input_name] );
        }
        // trigger form re-calculation
        $('#user_actual').text(pricing_item_meta_data.length_needed*pricing_item_meta_data.width_needed);
        $( 'form.cart' ).trigger( 'wc-measurement-price-calculator-update' );
        // trigger manual change event after refill
        setTimeout( (function( $input ) {
            return function() {
                $input.trigger( 'mpc-change' );
            };
        })( $form.find( 'input.amount_needed:first' ) ), 100 );
    }

    $(".single_add_to_cart_button").text("Update cart");
    var $cart=$( 'form.cart' );
    product_refill_inputs($cart);

    if (typeof pricing_item_meta_data.extended_warrenty !== 'undefined') {
        $('.warranties').show();  
        $('#warrenty-check').attr('checked',true);
        $('.warranties input').prop("disabled", false);  
        $(":radio[value="+pricing_item_meta_data.extended_warrenty+"]").attr('checked',true);
    }    
});
