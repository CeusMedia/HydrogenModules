<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Manage_Form_Fill extends AjaxController
{
	public function findEmail(): void
	{
		$query	= trim( $this->env->getRequest()->get( 'q', '' ) );

		if( strlen( $query ) < 3 )
			$this->respondData( [] );

		$conditions	= ['email' => '% %'.$query.'%'];
		$orders		= ['email' => 'ASC'];
		$limits		= [0, 10];

		$model	= new Model_Form_Fill( $this->env );
		$emails	= $model->getDistinct( 'email', $conditions, $orders, $limits );
		$this->respondData( $emails );
	}
}