var _gaq = _gaq || [];
function initGoogleAnalytics(options){
	_gaq.push(['_setAccount', options.trackingID]);
	if(options.anonymizeIP)
		_gaq.push(['_gat._anonymizeIp']);
	_gaq.push(['_trackPageview']);
	var ga = document.createElement('script');
	ga.type = 'text/javascript';
	ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0];
	s.parentNode.insertBefore(ga, s);
}
