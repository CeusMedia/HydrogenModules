<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;
use CeusMedia\HydrogenFramework\View;

class Hook_Shop_FinishPanel extends Hook
{
	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env			Environment instance
	 *	@param		object			$context		Hook context object
	 *	@param		object			$module			Module object
	 *	@param		array			$payload		Map of hook arguments
	 *	@return		void
	 */
	public static function onRenderServicePanels( Environment $env, object $context, object $module, array & $payload ): void
	{
		if( empty( $payload['orderId'] ) || empty( $payload['paymentBackends']->getAll() ) )
			return;
		$view		= new View( $env );
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
				$context->registerServicePanel( 'Shop:'.$priority, $content, $priority );
			}
		}
	}
}
