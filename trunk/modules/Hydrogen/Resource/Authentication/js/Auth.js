var Auth = {

	oneMinute: 60 * 1000,

	initAutoLogout: function(){
		var config	= settings.Resource_Authentication;
		var minutes = parseInt(config.logout_auto_minutes);
		if(!config.logout_auto || !minutes)
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
						if(config.logout_auto_forward_controller){
							url += '/'+config.logout_auto_forward_controller;
							if(config.logout_auto_forward_action)
								url += '/'+config.logout_auto_forward_action;
						}
						url += '?autoLogout';
						document.location.href = url;
					}, duration
				);
			}
		});
	},

	initSessionRefresh:function(){
		var config	= settings.Resource_Authentication;
		var minutes = parseInt(config.session_refresh_minutes);
		if(!config.session_refresh || !minutes)
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
