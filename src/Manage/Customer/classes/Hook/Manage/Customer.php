<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Customer extends Hook
{
	public function onRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'manage/customer' );						//  load words
		View_Manage_Customer::registerTab( 'edit/'.$this->payload['customerId'], $words->tabs['edit'], 0 );	//  register main tab
		if( $this->env->getModules()->has( 'UI_Map' ) ){												//  map module is enabled
			$model		= new Model_Customer( $this->env );												//  get customer model
			$customer	= $model->get( $this->payload['customerId'] );										//  get customer data
			$disabled	= !$customer || (bool) !$customer->latitude;								//  no customer or customer not geocoded
			$label		= $words->tabs['map'];														//  get tab label
			View_Manage_Customer::registerTab( 'map/'.$this->payload['customerId'], $label, 2, $disabled );	//  register map tab
		}
	}
}