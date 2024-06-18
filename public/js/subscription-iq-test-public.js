(function( $ ) {
	'use strict';
	$(document).ready(function($) {
	
		

            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                data: {
                    action: 'add_to_cart_and_redirect',
                    product_id: product_id,
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = '<?php echo wc_get_checkout_url(); ?>';
                    } else {
                        alert('Error al agregar el producto al carrito.');
                    }
                }
            });
        
		
	});
	
	

})( jQuery );
