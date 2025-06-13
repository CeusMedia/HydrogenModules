<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_My_User extends Hook
{
	/**
	 *	@return		void
	 */
	public function onRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'manage/my/user' );			//  load words
		$this->context->registerTab( '', $words->tabs['user'], 0 );									//  register main tab

		if( $this->env->getAcl()->has( 'manage/my/user', 'password' ) )
			$this->context->registerTab( 'password', $words->tabs['password'], 10 );					//  register main tab

		if( $this->env->getAcl()->has( 'manage/my/user', 'remove' ) )
			$this->context->registerTab( 'remove', $words->tabs['remove'], 99 );					//  register main tab

/*		if( $this->env->getModules()->has( 'UI_Map' ) ){											//  map module is enabled
			$model		= new Model_Customer( $this->env );											//  get customer model
			$customer	= $model->get( $this->payload['customerId'] );								//  get customer data
			$disabled	= !$customer || (bool) !$customer->latitude;								//  no customer or customer not geocoded
			$label		= $words->tabs['map'];														//  get tab label
			$this->context->registerTab( 'map/'.$data['customerId'], $label, 2, $disabled );		//  register map tab
		}*/
	}
}
