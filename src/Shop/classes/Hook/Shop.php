<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Shop extends Hook
{
	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@return		void
	 */
	public function onLogout(): void
	{
		$model	= new Model_Shop_Cart( $this->env );
		$model->set( 'orderStatus', Model_Shop_Order::STATUS_NEW );
		$model->remove( 'userId' );
		$model->remove( 'customerMode' );
		$model->remove( 'acceptRules' );
		$model->remove( 'paymentMethod' );
	}
}