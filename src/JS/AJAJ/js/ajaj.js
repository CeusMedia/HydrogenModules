/**
 *	@todo	implement error handling
 */
var AJAJ = {
	get: function(url, onSuccess, onError, options){
		onError	= onError || function(){};
		onSuccess = onSuccess || function(){};
		options = $.extend({
			url: url,
			method: "GET",
			dataType: "json",
			headers: {'X-Hydrogen-Client': 'AJAJ'}
		}, options);
		options = $.extend(options, {
			success: function(json){
				if(json.status == "ok"){
					data = json.data;
					onSuccess(data);
				}
			},
			error: function(a,b){
				console.log(a);
				console.log(b);
				alert( "AJAX Error: ");
				onError();
			}
		});
		$.ajax(options);
	},
	post: function(url, data, onSuccess, onError, options){
		options = $.extend(options, {
			method: "POST",
			data: data
		});
		AJAJ.get(url, onSuccess, onError, options);
	}
};

