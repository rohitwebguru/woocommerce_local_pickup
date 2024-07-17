jQuery( document ).ready( function( $ ) {    
	jQuery( '.input-text.qty' ).attr( 'readonly', 'readonly' );
	function update_user_area(){
		$( '.amount_user_area' ).each( function( index, el ) {
			el = $( el );

			length=$( '#length_needed' ).val();
			width=$( '#width_needed' ).val();

			amount=parseFloat( (length * width ).toFixed( 2 ) );

			if ( el.is( 'input' ) ) {
				el.val( amount );
			} else {
				el.text( amount );
			}
		});
	}

	update_user_area();

	$( '#length_needed, #width_needed' ).blur(function(){
			update_user_area();
	});

	$( '#length_needed, #width_needed' ).change(function(){
			update_user_area();
	});

	$( '#length_needed, #width_needed' ).keyup(function(){
			update_user_area();
	});
	$( '.qty' ).change(function(){
			update_user_area();
	});
});
