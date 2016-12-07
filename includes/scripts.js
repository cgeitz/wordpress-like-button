jQuery(document).ready(function($) {

	// console.log(ajax_object.is_logged_in);
	var like_button_html = '<li class="share-like"><span></span></li>';

	$('.share-end').before(like_button_html);

	$('.btc-likes').on('click', '.btc-not-liked', function(){

		console.log('not liked clicked');

		var id = $('.btc-likes').data( "post-id" );
		$.ajax({
			url: ajax_object.ajax_url,
			type: "POST",
			data:{
			  'action' : 'btc_like_post',
			  'id'     : id
			},
			success:function(data){
				console.log('liked');
				console.log('logged in? ' + ajax_object.is_logged_in);

				$('.data-holder').addClass(data);

			}
		});

	});

	$('.btc-likes').on('click', '.btc-liked', function(){

		console.log('liked clicked');

		var id = $('.btc-likes').data( "post-id" );

		$.ajax({
			url: ajax_object.ajax_url,
			type: "POST",
			data:{
			  'action' : 'btc_unlike_post',
				'id'     : id
			},
			success:function(data){
				console.log('un liked');
				$('.data-holder').addClass(data);
			}
		});

	});

	var like_count = $('.like-count').data( "likes" );
	$('.btc-likes').on('click', '.btc-heart', function(){


		console.log(like_count);

		if (ajax_object.is_logged_in == 'true'){
			if( $(this).hasClass('btc-liked') ) {
				like_count --;
			}else{
				like_count ++;
			}
				$('.like-count').html(' ' + like_count + ' LIKES');
		}

		if (ajax_object.is_logged_in == 'false'){
			$('.optional-login-container').removeClass('modal-closed').addClass('modal-opened');
		}
		else {
			$('.btc-heart').toggleClass('btc-liked btc-not-liked');
		}





	});


});
