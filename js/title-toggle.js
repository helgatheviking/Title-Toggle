/*
 Script for toggling the title display in the quick edit
 */
(function($) {

	$('#the-list').on('click', '.editinline', function(){

		// reset
		inlineEditPost.revert();

		tag_id = $(this).parents('tr').attr('id');

		toggle_state = $( '.title-toggle', '#' + tag_id ).data('title_toggle');

		checked = ( 'hide' == toggle_state ) ? true : false;
		
		// get the value and check the correct input
		$( 'input.title-toggle-input', '.quick-edit-row' ).prop( 'checked', checked );

	});

	$('#the-list').on('click', 'a.title-toggle', function(e){

		e.preventDefault();

		var link = $(this);

		link.hide().before('<span class="spinner">');
		link.prev('.spinner').css({'float':'left','visibility':'visible','margin-left':'0'}).show();

		$.ajax({
			url: ajaxurl,
			data: { _wpnonce: link.data('nonce'), post_id: link.data('post_id'), action: 'title_toggle_quickedit' }
		}).done(function(response) {
			//console.log(response);
			if( 'hide' == response ){
				link.removeClass('dashicons-yes').addClass('dashicons-no-alt').data('title_toggle', 'hide' );
			} else if( 'show' == response ) {
				link.removeClass('dashicons-no-alt').addClass('dashicons-yes').data('title_toggle', 'show' );
			}
			link.prev('.spinner').remove();
			link.fadeIn();
		});



	});

})(jQuery);