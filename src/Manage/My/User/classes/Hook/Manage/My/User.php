<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_My_User extends Hook
{
	public function onRegisterTab()
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'manage/my/user' );			//  load words
		$this->context->registerTab( '', $words->tabs['user'], 0 );									//  register main tab
/*		if( $this->env->getModules()->has( 'UI_Map' ) ){											//  map module is enabled
			$model		= new Model_Customer( $this->env );											//  get customer model
			$customer	= $model->get( $this->payload['customerId'] );								//  get customer data
			$disabled	= !$customer || (bool) !$customer->latitude;								//  no customer or customer not geocoded
			$label		= $words->tabs['map'];														//  get tab label
			$this->context->registerTab( 'map/'.$data['customerId'], $label, 2, $disabled );		//  register map tab
		}*/
	}
}