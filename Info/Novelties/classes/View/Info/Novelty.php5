<?php
class View_Info_Novelty extends CMF_Hydrogen_View{

	public function ajaxRenderDashboardPanel(){
		$helper		= new View_Helper_Info_Novelty_DashboardPanel( $this->env );
		$helper->setLimit( 10 );
//		$helperNews	= new View_Helper_NewsList( $this->env );
//		$helperNews->collect( 'Page', 'collectNews', array() );
		return $helper->render();
	}
}
?>
