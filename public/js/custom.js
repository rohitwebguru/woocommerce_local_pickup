var current_location=null;
jQuery.noConflict();
jQuery(document).ready(function($){
    var check_error = 0;
    var check_different_form =  0;
    if(pickpup_cart_discount.delivery=='same' || pickpup_cart_discount.delivery=='different_address'){
        $("#pickup_location_addresss_field").hide();
        $("#local-pick-up-map").hide();
        $("#local-pick-up-selected-map").hide();
        $(".fee").hide();
    }
    if(pickpup_cart_discount.delivery=='different_address'){
        jQuery("#ship-to-different-address-checkbox").trigger("click");
    }
    $('#ship_address').hide();
    $('#ship_address h3').hide();
    $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide(); 
    $('#pickup_location_addresss_field').hide();
    $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide();
    $('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').hide();
    jQuery( document.body ).on( 'updated_checkout', function(){
        $('#ship_address').hide();

        if( current_index ==  1){
            if( $( "input[name='kvell_delivery']" ).is(":checked")){         
                var selected_pickup = $("input[name='kvell_delivery']:checked");  
                if( selected_pickup.length >  0 ){
                    pickup_value  = selected_pickup.val();
                    console.log( pickup_value );
                    if(pickup_value == 'different_address'){
                        $( '#ship_address' ).show();
                        $('#ship_address h3').show();
                        $('#ship_address .woocommerce-shipping-fields__field-wrapper').show();    
                    }else  if(pickup_value == 'local_pickup'){                        
                        $('#pickup_location_addresss_field').show();
                        $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').show();
                    }else{
                        $('#ship_address h3').hide();
                        $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();
                        $('#pickup_location_addresss_field').hide();
                        $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide();    
                    }
                }
            }
        }        
    });
    
    $("#kvell_delivery_local_pickup, #kvell_delivery_same, #kvell_delivery_different_address").click(function(){
        var val=$(this).val();

        if(val == 'different_address'){
            $( '#ship_address' ).show();
            $('#ship_address h3').show();
            $('#ship_address .woocommerce-shipping-fields__field-wrapper').show();                
            $('#pickup_location_addresss_field').hide();
            $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide(); 
        }else  if(val == 'local_pickup'){                        
            $('#pickup_location_addresss_field').show();
            $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').show();
            $(".fee").show();
            $( '#ship_address h3').hide();
            $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();
        }else{
            $('#ship_address h3').hide();
            $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();
            $('#pickup_location_addresss_field').hide();
            $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide();    
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
                jQuery('body').trigger('update_checkout')
            },
            error: function(error){
            }
        });
    }

    $('input[type="radio"]').bind('click', function(){
        // Processing only those that match the name attribute of the currently clicked button...
        $('input[name="' + $(this).attr('name') + '"]').not($(this)).trigger('deselect'); // Every member of the current radio group except the clicked one...
    });

    var current_index =  0;

    $('.checkout-steps li:nth-child(1)').click(function(){  
        current_index = 0;          
        $('.woocommerce-billing-fields__field-wrapper p').show();
		$('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').hide();
        $('#kvell_delivery_field').hide();
        $('#pickup_location_addresss_field').hide();
        $('#order-review').hide();
        $( '#next_step' ).show();
        $('#ship_address').hide();
        $('#ship_address h3').hide();
        $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide(); 
        $('#pickup_location_addresss_field').hide();
        $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide();   
        $('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').hide();   
        $(".fee").hide();        
    });    

    $('.checkout-steps li:nth-child(2)').click(function(e){
        e.preventDefault();
        $( '#next_step' ).show();
        //current_index = 1;      
        console.log( $( this ).find( 'a' ).hasClass( 'always-active' ) );          
        console.log( current_index );
		$('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').hide();
        if( $( this ).find( 'a' ).hasClass( 'always-active' ) == true ){
            current_index = 1;            
            $('.woocommerce-billing-fields__field-wrapper p').hide();
            $('#order-review').hide();
            $('#kvell_delivery_field').show();
            $('.woocommerce-billing-fields h3').hide();  
            var selected_pickup = $("input[name='kvell_delivery']:checked").val();  

            if(selected_pickup == 'different_address'){
                $( '#ship_address' ).show();
                $('#ship_address h3').show();
                $('#ship_address .woocommerce-shipping-fields__field-wrapper').show();                
                $('#pickup_location_addresss_field').hide();
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide(); 
            }else  if(selected_pickup == 'local_pickup'){                        
                $('#pickup_location_addresss_field').show();
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').show();
                $(".fee").show();
                $( '#ship_address h3').hide();
                $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();
            }else{
                $('#ship_address h3').hide();
                $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();
                $('#pickup_location_addresss_field').hide();
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide();    
            }
        }else if( current_index > 0  ){
            current_index = 1;            
            $('.woocommerce-billing-fields__field-wrapper p').hide();
            $('#order-review').hide();
            $('#kvell_delivery_field').show();
            $('.woocommerce-billing-fields h3').hide(); 
					
            var selected_pickup = $("input[name='kvell_delivery']:checked").val();  

            if(selected_pickup == 'different_address'){
                $( '#ship_address' ).show();
                $('#ship_address h3').show();
                $('#ship_address .woocommerce-shipping-fields__field-wrapper').show();                
                $('#pickup_location_addresss_field').hide();
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide(); 
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').hide(); 
            }else  if(selected_pickup == 'local_pickup'){                        
                $('#pickup_location_addresss_field').show();
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').show();	
                $(".fee").show();
                $( '#ship_address h3').hide();
                $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();
            }else{
                $('#ship_address h3').hide();
                $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();
                $('#pickup_location_addresss_field').hide();
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide();    
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').hide();    
            }
        }else{
            $( this ).find( 'a' ).removeClass( 'always-active' );
            return false;
        }         
    });

    $('.checkout-steps li:nth-child(3)').click(function(e){
        //current_index = 2;
        e.preventDefault();
        $( '#previous_step' ).show();
        var selected_pickup = $("input[name='kvell_delivery']:checked").val();  
		$('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide(); 
        if( $( this ).find( 'a' ).hasClass( 'always-active' ) == true ){
            console.log(current_index);
            current_index = 2;
            $( '#next_step' ).hide();
            $('.woocommerce-billing-fields__field-wrapper p').hide();
            $('#order-review').show();
            $('.woocommerce-billing-fields h3').hide();    
            $('#ship_address h3').show();
            $('#ship_address .woocommerce-shipping-fields__field-wrapper').show();        

            if(selected_pickup == 'different_address'){
                $( '#ship_address' ).show();
                $('#ship_address h3').show();
                $('#ship_address .woocommerce-shipping-fields__field-wrapper').show();                
                $('#pickup_location_addresss_field').hide();
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide(); 
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').hide(); 
            }else  if(selected_pickup == 'local_pickup'){                        
                //$('#pickup_location_addresss_field').show();
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').show();
                $(".fee").show();
                $( '#ship_address h3').hide();
                $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();
            }else{
                $('#ship_address h3').hide();
                $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();
                $('#pickup_location_addresss_field').hide();
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide();    
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').hide();    
            }     
        }else if( current_index >= 1 ){
            console.log(current_index);
            current_index = 2;
            $( '#next_step' ).hide();
            $('.woocommerce-billing-fields__field-wrapper p').hide();
            $('#order-review').show();
            $('.woocommerce-billing-fields h3').hide();    

            if(selected_pickup == 'different_address'){
                $( '#ship_address' ).show();
                $('#ship_address h3').show();
                $('#ship_address .woocommerce-shipping-fields__field-wrapper').show();                
                $('#pickup_location_addresss_field').hide();
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide(); 
            }else  if(selected_pickup == 'local_pickup'){                        
                //$('#pickup_location_addresss_field').show();
                //$('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').show();
                $(".fee").show();
                $( '#ship_address h3').hide();
                $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();
            }else{
                $('#ship_address h3').hide();
                $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();
                $('#pickup_location_addresss_field').hide();
                $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide();    
            }
        }else{            
            $( this ).find( 'a' ).removeClass( 'always-active' );
            return false;
            
        }        
    });    
    var headingNav = $( '.checkout-steps' );
    $( '#previous_step' ).hide();    
    $( document ).on( 'click', '#previous_step',function(e){
        e.preventDefault();
        current_index = current_index-1;
        if( current_index == 0 ){
            $('.woocommerce-billing-fields__field-wrapper p').show();
			$('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').hide();
            $('#kvell_delivery_field').hide();
            $('#pickup_location_addresss_field').hide();
            $('#order-review').hide();
            $( this ).hide();
            $( '#next_step' ).show();
            $( '.checkout-steps li' ).eq( current_index ).find( 'a' ).attr( 'class','always-active' );              
            $('#ship_address h3').hide();
            $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();    
            $('#pickup_location_addresss_field').hide();
            $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide();                       
        }else if( current_index == 1 ){
            $('.woocommerce-billing-fields__field-wrapper p').hide();
			$('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').hide();
            $('#order-review').hide();
            $('#kvell_delivery_field').show();
            $('.woocommerce-billing-fields h3').hide();
            $( '#next_step' ).show();
            $( '.checkout-steps li' ).eq( current_index ).find( 'a' ).attr( 'class','always-active' );
            if( $( "input[name='kvell_delivery']" ).is(":checked")){         
                var selected_pickup = $("input[name='kvell_delivery']:checked");  
                if( selected_pickup.length >  0 ){
                    pickup_value  = selected_pickup.val();
                    console.log( pickup_value );
                    if(pickup_value == 'different_address'){
                        $( '#ship_address' ).show();
                        $('#ship_address h3').show();
                        $('#ship_address .woocommerce-shipping-fields__field-wrapper').show();    
                    }else  if(pickup_value == 'local_pickup'){                        
                        $('#pickup_location_addresss_field').show();
                        $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').show();
                    }else{
                        $('#ship_address h3').hide();
                        $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();
                        $('#pickup_location_addresss_field').hide();
                        $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide();    
                    }
                }
            }
        }else if( current_index == 2 ){
            $('.woocommerce-billing-fields__field-wrapper p').hide();
            $('#order-review').show();
            $('.woocommerce-billing-fields h3').hide();            
            $( '.checkout-steps li' ).eq( current_index ).find( 'a' ).attr( 'class','always-active' );
            $('#ship_address h3').hide();
            $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide(); 
        }  
        return false;         
    });
    
    $( document ).on( 'click', '#next_step',function(e){  
        e.preventDefault();
		console.log("current_index",current_index);
        if( current_index <  1 ){
            var billing_first_name =         $('#billing_first_name').val();
            var billing_last_name =          $('#billing_last_name').val();
            var billing_address_1 =          $('#billing_address_1').val();
            var billing_address_2_field =    $('#billing_address_2_field').val();
            var billing_city =               $('#billing_city').val();
            var billing_postcode =           $('#billing_postcode').val();
            var billing_phone =              $('#billing_phone').val();
            var billing_email =              $('#billing_email').val();           

            check_error = 0;

            if (billing_first_name.length < 1) {
                $('#billing_first_name').after('<span id="billing_first_error" class="error">This field is required</span>');
                check_error = 1;              
            }else{
                $('#billing_first_error').remove();                                
            }
            
            if (billing_last_name.length < 1) {
                $('#billing_last_name').after('<span id="billing_last_error" class="error">This field is required</span>');
                check_error = 1;  
             }else{
                $('#billing_last_error').remove();             
            }
            
            if(billing_address_1 < 1){
                $('#billing_address_1').after('<span id="billing_address_error" class="error">This field is required</span>');
                check_error = 1;                      
            }else{
                $('#billing_address_error').remove();               
            }

            if(billing_city < 1){
                $('#billing_city').after('<span id="billing_city_error" class="error">This field is required</span>');                
                check_error = 1;               
            }else{
                $('#billing_city_error').remove();               
            }

            if(billing_postcode < 1){
                $('#billing_postcode').after('<span id="billing_postcode_error" class="error">This field is required</span>');
                check_error = 1;                
            }else{
                $('#billing_postcode_error').remove();                
            }

            if(billing_phone < 1){
                $('#billing_phone').after('<span id="billing_phone_error" class="error">This field is required</span>');
                check_error = 1;                  
            }else{
                $('#billing_phone_error').remove();                
            }

            if(billing_email < 1){
                $('#billing_email').after('<span id="billing_email_error" class="error">This field is required</span>');
                check_error = 1;               
            }else {
                $('#billing_email_error').remove();   

                if (!isEmail( billing_email )) {
                  $('#billing_email').after('<span id="billing_email_valid_error" class="error">Enter a valid email</span>');
                  check_error = 1;
                }else{
                    $('#billing_email_valid_error').remove();                    
                }
            }
            if( check_error != 0  ){
                return false;
            }

            var geo_address =  billing_address_1+"+"+billing_address_2_field+"+"+billing_city+"+"+billing_postcode
            var geo_ajax_url = 'https://maps.googleapis.com/maps/api/geocode/json?address='+geo_address+'&key=AIzaSyBUswWMnnqwD-SoBFUD_KxzWBnVq53aEe8';
            jQuery.ajax({url: geo_ajax_url, success: function(result){
				current_location=result.results[0].geometry.location;
				initMap();
                //console.log( result.results[0].geometry.location );
            }});

            current_index =current_index + 1;
            get_location_and_pickups();
            $('.woocommerce-billing-fields__field-wrapper p').hide();
            $('#kvell_delivery_field').show();            
            $('#order-review').hide();
            $( '#previous_step' ).show();
            $( '.checkout-steps li' ).eq( current_index ).find( 'a' ).attr( 'class','always-active' );              

            var selected_pickup = $("input[name='kvell_delivery']:checked").val(); 
            if( selected_pickup.length >  0 ){            
                if(selected_pickup == 'different_address'){
                    $( '#ship_address' ).show();
                    $('#ship_address h3').show();
                    $('#ship_address .woocommerce-shipping-fields__field-wrapper').show();    
                }else  if(selected_pickup == 'local_pickup'){                
                    $('#pickup_location_addresss_field').show();
                    $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').show();
					$('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').hide(); 
                }else{
                    $('#ship_address h3').hide();
                    $('#ship_address .woocommerce-shipping-fields__field-wrapper').hide();
                    $('#pickup_location_addresss_field').hide();
                    $('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide();    
                    $('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').hide();    
                }
            }

        }else if( current_index == 1 ){			
			$('.woocommerce-billing-fields__field-wrapper #local-pick-up-map').hide();    
			$('.woocommerce-billing-fields__field-wrapper #local-pick-up-selected-map').show(); 
            if( $( "input[name='kvell_delivery']" ).is(":checked")){ 
                var selected_pickup = $("input[name='kvell_delivery']:checked");  
                if( selected_pickup.length >  0 ){
                    pickup_value  = selected_pickup.val();
                    if(pickup_value=='different_address'){
                        var shipping_first_name =        $('#shipping_first_name').val();
                        var shipping_last_name  =        $('#shipping_last_name').val();
                        var shipping_address_1 =         $('#shipping_address_1').val();
                        var shipping_city      =         $('#shipping_city').val();   
                        var shipping_postcode   =        $('#shipping_postcode').val();
                        check_different_form = 0; 
                        
                        if (shipping_first_name.length < 1) {
                            $('#shipping_first_name').after('<span id="shipping_first_error" class="error">This field is required</span>');
                            check_different_form = 1;              
                        }else{
                            $('#shipping_first_error').remove();                                
                        }
                        if (shipping_last_name.length < 1) {
                            $('#shipping_last_name').after('<span id="shipping_last_error" class="error">This field is required</span>');
                            check_different_form = 1;              
                        }else{
                            $('#shipping_last_error').remove();             
                        }
                        if(shipping_address_1 < 1){
                            $('#shipping_address_1').after('<span id="shipping_address_error" class="error">This field is required</span>');
                            check_different_form = 1;              
                        }else{
                            $('#shipping_address_error').remove();               
                        }
                        if(shipping_city < 1){
                            $('#shipping_city').after('<span id="shipping_city_error" class="error">This field is required</span>');                
                            check_different_form = 1;              
                        }else{
                            $('#shipping_city_error').remove();               
                        }                
                        if(shipping_postcode < 1){
                            $('#shipping_postcode').after('<span id="shipping_postcode_error" class="error">This field is required</span>');
                            check_different_form = 1;              
                        }else{
                            $('#shipping_postcode_error').remove();                
                        }
                    }
                } 
                if( check_different_form == 0 ){
                    current_index =current_index + 1;                    
                    check_different_form = 0;  
                    
                    $('.woocommerce-billing-fields__field-wrapper p').hide();
                    $('#order-review').show();
                    $('.woocommerce-billing-fields h3').hide();
                    $( this ).hide();
                    $( '.checkout-steps li' ).eq( current_index ).find( 'a' ).attr( 'class','checkout-step always-active' );
                    $( '#previous_step' ).show();                     

                    if (current_index > 1 ){
                        $('.woocommerce-billing-fields__field-wrapper p').hide();
                        $('#order-review').show();
                        $('.woocommerce-billing-fields h3').hide();            
                        $( '.checkout-steps li' ).eq( current_index ).find( 'a' ).attr( 'class','always-active' );
                        $('#ship_address h3').show();
                        $('#ship_address .woocommerce-shipping-fields__field-wrapper').show();
                    }                    
                }
            }else{
                current_index =current_index + 1;
                check_different_form = 0;  
                $('.woocommerce-billing-fields__field-wrapper p').hide();
                $('#order-review').show();
                $('.woocommerce-billing-fields h3').hide();
                $( this ).hide();
                $( '.checkout-steps li' ).eq( current_index ).find( 'a' ).attr( 'class','checkout-step always-active' );
                $( '#previous_step' ).show();
            }            
        }
        return false;         
    });

    function get_location_and_pickups(){

    }

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }
});

