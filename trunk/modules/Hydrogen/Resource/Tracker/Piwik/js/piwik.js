function initPiwik(options){
	var pkProtocol = ("https:" == document.location.protocol) ? "https" : "http";
	var pkBaseURL = pkProtocol + "://" + options.URI;
	try {
		var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", options.ID);
		piwikTracker.trackPageView();
		piwikTracker.enableLinkTracking();
	} catch( err ) {}
}
