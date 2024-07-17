jQuery( function( $ ) {

    function reset_warrenties(){
        $('#extended_warrenty_options tbody tr').each(function(currentIndex, tr) { 
            $(this).find('input').each(function(index, input){
                if(index==0)
                {
                    value='extended_warrenty['+currentIndex+']';                                     
                }
                else if(index==1)
                {
                    value='extended_start_price['+currentIndex+']';
                }
                else if(index==2)
                {  
                    value='extended_end_price['+currentIndex+']';
                }                
                else if(index==3)
                {  
                    value='extended_warrenty_price['+currentIndex+']';
                }                
                $(this).attr('name',value);
            });           
         });
    }

    $( '.add_warrenty' ).on( 'click', function() {
        var i=jQuery('#extended_warrenty_options tbody tr').length;
        var tr;
        var symbol;
        symbol=extend_warrenty_data.symbol;
        tr+="<tr>";
        tr+='<td><input type="text" placeholder="Enter warrenty name" value="" name="extended_warrenty['+i+']" /></td>';
        tr+='<td><input type="number" placeholder="Enter start price" value="" name="extended_start_price['+i+']" /> '+symbol+'</td>';
        tr+='<td><input type="number" placeholder="Enter end price" value="" name="extended_end_price['+i+']" /> '+symbol+'</td>';
        tr+='<td><input type="number" placeholder="Enter warrenty Price" value="" name="extended_warrenty_price['+i+']">'+symbol+'<a href="javascript:void(0)" class="remove-warrenty">X</a></td>';
        tr+="</tr>";        
        $('#extended_warrenty_options tbody tr:last').after(tr);
        $( '.remove-warrenty' ).on( 'click', function() {
            $(this).parents('tr').remove();
            reset_warrenties();
        });
    });

    $( '.remove-warrenty' ).on( 'click', function() {
        $(this).parents('tr').remove();
        reset_warrenties();
    });

    $( '.save_extended_warrenty' ).on( 'click', function() {
        $("#extended_warrenty_options").block({
            message: null,
            overlayCSS: {
                background: "#fff",
                opacity: .6
            }
        });
        var data = {
        post_id     : extend_warrenty_data.product_id,    
        data        : $("#extended_warrenty_options :input").serialize(),
        action      : extend_warrenty_data.action
        }

        $.post( extend_warrenty_data.ajax_url, data, function( response ) {
            if ( response.error ) {
				// Error.
				window.alert( response.error );
			} else if ( response ) {
                // Success.
                $("#extended_warrenty_options").unblock();            
            }
        });
    });

    



   // $('#myTable tr:last').after('<tr>...</tr><tr>...</tr>');

});