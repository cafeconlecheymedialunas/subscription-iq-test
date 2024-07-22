(function( $ ) {
	'use strict';
	$(document).ready(function($) {
	
		console.log(ajax_object)
	
		$('.subscription-form').on('submit', function(e) {
            e.preventDefault();

			var $form = $(this);
			var $button = $form.find('.subscribe-button');
			var $spinner = $button.find('.spinner');
			var product_id = $form.find('input[name="product_id"]').val();
			var result_id = $form.find('input[name="result_id"]').val();
			$spinner.show();
			$button.prop('disabled', true);

			$.ajax({
				type: 'POST',
				url: ajax_object.ajax_url,
				data: {
					action: 'add_to_cart_and_redirect',
					product_id: product_id,
					result_id:result_id
				},
				success: function(response) {
					$spinner.hide();
					if (response.success) {
						window.location.href = ajax_object.checkout_url;
						//console.log(ajax_object)
					} else {
						console.log(response)
						alert(response.data || 'Error adding product to cart.');
						$button.prop('disabled', false);
					}
				},
				error: function() {
					$spinner.hide();
					alert('Error adding product to cart.');
					$button.prop('disabled', false);
				}
			});
    
        });

		$('.cancel-subscription').on('submit', function(e) {
            e.preventDefault();

			var $form = $(this);
			var $button = $form.find('.subscribe-button');
			var $spinner = $button.find('.spinner');
			var product_id = $form.find('input[name="product_id"]').val();
			$spinner.show();
			$button.prop('disabled', true);

			$.ajax({
				type: 'POST',
				url: ajax_object.ajax_url,
				data: {
					action: 'cancel_subscription',
					product_id: product_id,
				},
				success: function(response) {
					$spinner.hide();
					if (response.success) {
						window.location.href = ajax_object.subscription_plan_url;
						//window.location.href = ajax_object.checkout_url;
						console.log(response)
					} else {
						console.log(response)
						alert(response.data || 'Error canceling subscription.');
						$button.prop('disabled', false);
					}
				},
				error: function() {
					$spinner.hide();
					alert('Error canceling subscription.');
					$button.prop('disabled', false);
				}
			});
    
        });
    
	});
	
	

})( jQuery );
