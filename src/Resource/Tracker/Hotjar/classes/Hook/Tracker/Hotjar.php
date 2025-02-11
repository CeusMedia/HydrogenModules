<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Tracker_Hotjar extends Hook
{
	public function onPageApplyModules(): void
	{
		$config	= $this->env->getConfig()->getAll( 'module.resource_tracker_hotjar.', TRUE );				//  get module configuration as array map
		if( !$config->get( 'active' ) || !$config->get( 'ID' ) )									//  hotjar tracking is disabled or ID is not set
			return;

		$script	= "
//  Hotjar Tracking Code
(function(h,o,t,j,a,r){
	h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
	h._hjSettings={hjid:%d,hjsv:%d};
	a=o.getElementsByTagName('head')[0];
	r=o.createElement('script');r.async=1;
	r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
	a.appendChild(r);
})(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');";
		$script	= sprintf( $script, $config->get( 'ID' ), $config->get( 'version' ) );
		$this->context->js->addScriptOnReady( $script );
	}
}
