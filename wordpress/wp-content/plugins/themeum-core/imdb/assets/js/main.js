(function ($) {
	$("#add-movie").on('click', function (e) {
		e.preventDefault();

		var imdb_id = $("#imdb-movie").val(),
			loader = $("#tm-imdb-spinner"),
			message = $("#tm-imdb-message");

		message.removeClass("error");
		message.removeClass("updated");

		message.hide();

		if (!imdb_id.length) {
			alert("Please enter a valid movie ID.");

			return;
		}

		loader.show();

		$.ajax({
			url: ajaxurl,
			method: "POST",
			dataType: "json",
			data: {
				'action': 'tm_add_movie_from_imdb',
				'imdb_id': imdb_id
			},
			success: function (data) {
				console.log(data);

				if (data.type == 'error') {
					message.addClass("error");
					message.html(data.message);

					message.show();
				} else if(data.type == 'success') {
					message.addClass("updated");
					message.html(data.message);

					message.show();
				} else {
					message.html('Unknown Error');

					message.show();
				}

				/*setTimeout(function () {
					message.hide();
				}, 5000);*/

				loader.hide();
			},
			error: function (data) {

				message.addClass("error");
				
				message.html('Unknown Error');

				message.show();

				/*setTimeout(function () {
					message.hide();
				}, 5000);*/

				loader.hide();
			}
		});
	});
})(jQuery);