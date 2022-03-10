(function($, params){
	$("#contactForm").submit(function(e){
		$.post(params.destinationURL, { 
			name: $("#name").val(), 
			email: $("#email").val(), 
			message: $("#message").val() 
		}).done(function( data ) {
			$("#success").html(data);
		});
		$('#contactForm')[0].reset();
		e.preventDefault();
	});
})(jQuery, contactFormParams);
