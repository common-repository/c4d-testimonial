var teamTestimonialSlider = {};
(function($){
	$(document).ready(function(){
		var defaultParams = {
			// Most important owl features
		    items : 4,
		    singleItem : true,
		    
		    //Autoplay
		    autoPlay : false,
		    stopOnHover : false,
		 
		    // Navigation
		    navigation : true,
		    scrollPerPage : false,
		    navigationText: ['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
		 
		    //Pagination
		    pagination : true,
		    paginationNumbers: false,
		 	
		 	//Auto height
		    autoHeight : true,
		    lazyLoad : true
    	};

		$(".c4d-testimonial").each(function(){
			var id = $(this).find('.c4d-testimonial__slider > div').attr('id'),
			self = this,
			params = teamTestimonialSlider[id];

			$.each(defaultParams, function(index, value){
				if(typeof params[index] != 'undefined') {
					if (params[index] == 'false') {
						defaultParams[index] = false;
					} else if (params[index] == 'true') {
						defaultParams[index] = true;
					} else {
						defaultParams[index] = params[index];	
					}
				}
			});
			
			$('#' + id).owlCarousel(defaultParams);
		});
	});
})(jQuery);