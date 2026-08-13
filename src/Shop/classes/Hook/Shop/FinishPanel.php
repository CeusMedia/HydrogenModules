<?php

use CeusMedia\HydrogenFramework\Hook;
use CeusMedia\HydrogenFramework\View;

class Hook_Shop_FinishPanel extends Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onRenderServicePanels(): void
	{
		$payload	= $this->getPayload() ?? [];
		if( empty( $payload['orderId'] ) || empty( $payload['paymentBackends']->getAll() ) )
			return;
		$view		= new View( $this->env );
//		$modelOrder	= new Model_Shop_Order( $env );
//		$order		= $modelOrder->get( $payload['orderId'] );

		$path	= 'html/shop/panel/';
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
				$this->context->registerServicePanel( 'Shop:'.$priority, $content, $priority );
			}
		}
	}
}
