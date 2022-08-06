<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Info_Novelty extends AjaxController
{
	public function dismiss()
	{
		$userId	= Logic_Authentication::getInstance( $this->env )->getCurrentUserId();
		$model	= new Model_Novelty( $this->env );
		$data	= $this->env->getRequest()->getAllFromSource( 'POST', TRUE );
		$model->add( array(
			'userId'	=> $userId,
			'entryId'	=> $data->get( 'id' ),
			'type'		=> $data->get( 'type' ),
			'timestamp'	=> $data->get( 'timestamp' ),
		) );
		$this->respondData( TRUE );
	}

	public function renderDashboardPanel( $panelId )
	{
		$helper		= new View_Helper_Info_Novelty_DashboardPanel( $this->env );
		$helper->setLimit( 10 );
//		$helperNews	= new View_Helper_NewsList( $this->env );
//		$helperNews->collect( 'Page', 'collectNews', array() );
		$this->respondData( $helper->render() );
	}
}
