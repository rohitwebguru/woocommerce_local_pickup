jQuery( function($) {
    $('#warrenty-check').click(function(){
        if($(this).is(":checked")){
            $('.warranties').show();  
            $('.warranties input').prop("disabled", false);     
        }
        else if($(this).is(":not(:checked)")){
            $('.warranties').hide();
            $('.warranties input').prop("disabled", true);    
        }
    });


});