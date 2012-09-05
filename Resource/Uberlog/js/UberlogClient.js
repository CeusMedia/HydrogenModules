var UberlogClient = {

	client: 'CMF::UberlogClient/1.0',
	host: null,
	uri: null,

	record: function(data){
		if(!UberlogClient.uri){
			alert("No Uberlog uri defined.");
			return;
		}
		var data = $.extend({
			client: UberlogClient.client,
			userAgent: navigator.userAgent,
			host: UberlogClient.host,
			type: 1
		},data);
		$.ajax({
			url: UberlogClient.uri+'/record',
			data: data,
			type: "post",
			dataType: "json",
			success: function(response){
				console.log(response);
			}
		});
	}
};