<?php
class Controller_Info_Novelty extends CMF_Hydrogen_Controller{

	static public function ___onRegisterDashboardPanels( $env, $context, $module, $data = array() ){
        if( !$env->getAcl()->has( 'info/novelty', 'ajaxRenderDashboardPanel' ) )
            return;
		$context->registerPanel( 'info-novelty', array(
			'title'			=> 'Neuigkeiten',
			'heading'		=> 'Neuigkeiten',
			'url'			=> './info/novelty/ajaxRenderDashboardPanel',
			'rank'			=> 0,
			'refresh'		=> 10,
		) );
	}

	public function ajaxDismiss(){
		$userId	= Logic_Authentication::getInstance( $this->env )->getCurrentUserId();
		$model	= new Model_Novelty( $this->env );
		$data	= $this->env->getRequest()->getAllFromSource( 'POST', TRUE );
		$model->add( array(
			'userId'	=> $userId,
			'entryId'	=> $data->get( 'id' ),
			'type'		=> $data->get( 'type' ),
			'timestamp'	=> $data->get( 'timestamp' ),
		) );
		print( json_encode( TRUE ) );
		exit;
	}

	public function ajaxRenderDashboardPanel( $panelId ){
	}
}
?>
