var _paq = _paq || [];
var ModuleTrackerMatomo = {
	id: null,
	serverUrl: null,
	options: [],
	init: function(){
		if(!this.id)
			throw "No Matomo Site ID set.";
		if(!this.serverUrl)
			throw "No Matomo Server URL set.";
		/* tracker methods like "setCustomDimension" should be called before "trackPageView" */
		if(this.options.doNotTrack)
			_paq.push(["setDoNotTrack", true]);
		if(!this.options.cookies)
			_paq.push(["disableCookies"]);
		_paq.push(['trackPageView']);
		_paq.push(['enableLinkTracking']);
		_paq.push(['setTrackerUrl', this.serverUrl + 'piwik.php']);
		_paq.push(['setSiteId', this.id]);
		var g = document.createElement('script');
		var s = document.getElementsByTagName('script')[0];
		g.type = 'text/javascript';
		g.async = true;
		g.defer = true;
		g.src = this.serverUrl + 'piwik.js';
		s.parentNode.insertBefore(g, s);
	}
}
