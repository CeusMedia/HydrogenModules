var Auth = {

	oneMinute: 60 * 1000,

	initAutoLogout: function(){
		var minutes = parseInt(settings.Auth.autoLogout_minutes);
		if(!minutes)
			return;
		duration = minutes * this.oneMinute;
		$.ajax({
			url: './auth/ajaxIsAuthenticated',
			dataType: 'json',
			success: function(status){
				if(!status)
					return;
//				counter = minutes * 60;
//				window.setInterval(function(){counter--;console.log(counter)},1000);
				window.setInterval(
					function(){
						var url = './auth/logout';
						if(settings.Auth.autoLogout_forward_controller){
							url += '/'+settings.Auth.autoLogout_forward_controller;
							if(settings.Auth.autoLogout_forward_action)
								url += '/'+settings.Auth.autoLogout_forward_action;
						}
						url += '?autoLogout';
						document.location.href = url;
					}, duration
				);
			}
		});
	},

	initSessionRefresh:function(){
		var minutes = parseInt(settings.Auth.refreshSession_minutes);
		if(!minutes)
			return;
		duration = minutes * this.oneMinute;
		window.setInterval(
			function(){
				$.ajax({
					url: './auth/ajaxRefreshSession',
					dataType: 'html',
					type: 'POST'
				});
			}, duration
		);
	}
};
Auth.initSessionRefresh();
Auth.initAutoLogout();
