<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Hook;
use CeusMedia\HydrogenFramework\View;

class Hook_Info_Newsletter extends Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 *	@todo		finish implementation, extract to hook class and register in module config
	 *	@throws		ReflectionException
	 */
	public function onShopRenderServicePanels(): void
	{
		/** @var WebEnvironment $env */
		$env	= $this->env;

		if( empty( $this->payload['orderId'] ) || empty( $this->payload['paymentBackends']->getAll() ) )
			return;
		$view		= new View( $env );
//		$modelOrder	= new Model_Shop_Order( $env );
//		$order		= $modelOrder->get( $payload['orderId'] );

		$path	= 'html/info/newsletter/';
		$files	= [
			1	=> 'finishTop.html',
			3	=> 'finishAbove.html',
			5	=> 'finish.html',
			7	=> 'finishBelow.html',
			9	=> 'finishBottom.html',
		];
		foreach( $files as $priority => $file ){
			if( $view->hasContentFile( $path.$file ) ){
				$content	= $view->loadContentFile( $path.$file );
				$this->context->registerServicePanel( 'Newsletter:'.$priority, $content, $priority );
			}
		}

		$localeFile	= 'html/info/newsletter/finishPanel.html';
		if( $view->hasContentFile( $localeFile ) ){
			$content	= $view->loadContentFile( $localeFile );
			$this->context->registerServicePanel( 'Newsletter', $content, 8 );
		}
	}
}