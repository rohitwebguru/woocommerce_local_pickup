jQuery.noConflict();
var current_location=null;
jQuery(document).ready(function($){
    if(pickpup_cart_discount.delivery=='same' || pickpup_cart_discount.delivery=='different_address'){
        $("#pickup_location_addresss_field").hide();
        $("#local-pick-up-map").hide();
        $("#local-pick-up-selected-map").hide();
        $(".fee").hide();
    }
    if(pickpup_cart_discount.delivery=='different_address'){
        jQuery("#ship-to-different-address-checkbox").trigger("click");
    }
    $("#kvell_delivery_local_pickup, #kvell_delivery_same, #kvell_delivery_different_address").click(function(){
        var val=$(this).val();
        if(val=='local_pickup'){      
                $("#pickup_location_addresss_field").show();
                $("#local-pick-up-map").show();
                $("#local-pick-up-selected-map").show();
                $(".fee").show();
        }
        if($("#ship-to-different-address-checkbox").prop("checked") == false){
            jQuery("#ship-to-different-address-checkbox").trigger("click");
        }
        $.ajax({
            type: "post",
            url:  pickpup_cart_discount.ajax_url,
            data: {
                'action' : pickpup_cart_discount.ajax_action,
                'val' : val,
                'data':$('form.checkout').serialize()
            },
            success: function(response) {
                console.log('response: '+response); // just for testing | TO BE REMOVED
                jQuery('body').trigger('update_checkout');
            },
            error: function(error){
                console.log('error: '+error); // just for testing | TO BE REMOVED
            }
        });
    });
    var timer = null;
    $('form.checkout input').keydown(function(){
        clearTimeout(timer); 
        timer = setTimeout(doStuff, 1000)
    });
    $('form.checkout select').change(function(){
        doStuff();
    });
    $('#pickup_location_addresss').change(function(){
        doStuff();
    });
    function doStuff() {
        $.ajax({
            type: "post",
            url:  pickpup_cart_discount.ajax_url,
            data: {      
                'action' : pickpup_cart_discount.form_update,   
                'data':$('form.checkout').serialize()
            },
            success: function(response) {
                jQuery('body').trigger('update_checkout');
            },
            error: function(error){
            }
        });
    }

    $('input[type="radio"]').bind('click', function(){
        // Processing only those that match the name attribute of the currently clicked button...
        $('input[name="' + $(this).attr('name') + '"]').not($(this)).trigger('deselect'); // Every member of the current radio group except the clicked one...
    });

    $('input[type="radio"]').bind('deselect', function(){
        var val=$(this).val();
        if(val=='local_pickup'){
                $("#pickup_location_addresss_field").hide();
                $("#local-pick-up-map").hide();
                $("#local-pick-up-selected-map").hide();
                $(".fee").hide();          
        }
        if(val=='different_address'){
            if($("#ship-to-different-address-checkbox").prop("checked") == true){
                jQuery("#ship-to-different-address-checkbox").trigger("click");
            }    
        }
    });

    jQuery("#place_order").click(function(){
		jQuery("form[name='checkout']").submit();
	});
    // if (typeof wc_price_calculator_params !== 'undefined') {
    //     jQuery.cookie(wc_price_calculator_params.cookie_name,{length_needed: "1", width_needed: "1", quantity: "1"});
    //     wc_price_calculator_params.product_measurement_value=1;
    //     wc_price_calculator_params.cookie_name="reset";
    // }

});

