<?php
class Hook_Shop_FinishPanel
{
	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env			Environment instance
	 *	@param		object						$context		Hook context object
	 *	@param		object						$module			Module object
	 *	@param		public						$arguments		Map of hook arguments
	 *	@return		void
	 */
	public static function onRenderServicePanels( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		if( empty( $payload['orderId'] ) || empty( $payload['paymentBackends'] ) )
			return;
		$view		= new CMF_Hydrogen_View( $env );
//		$modelOrder	= new Model_Shop_Order( $env );
//		$order		= $modelOrder->get( $payload['orderId'] );

		$path	= 'html/shop/panel/';
		$files	= array(
			1	=> 'finishTop.html',
			3	=> 'finishAbove.html',
			5	=> 'finish.html',
			7	=> 'finishBelow.html',
			9	=> 'finishBottom.html',
		);
		foreach( $files as $priority => $file ){
			if( $view->hasContentFile( $path.$file ) ){
				$content	= $view->loadContentFile( $path.$file );
				$context->registerServicePanel( 'Shop:'.$priority, $content, $priority );
			}
		}
	}
}
