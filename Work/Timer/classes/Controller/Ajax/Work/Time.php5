<?php
class Controller_Ajax_Work_Time extends CMF_Hydrogen_Controller_Ajax
{
	public function renderDashboardPanel( $panelId )
	{
		switch( $panelId ){
			case 'work-timer-my':
				$helper		= new View_Helper_Work_Time_Dashboard_My( $this->env );
				break;
			case 'work-timer-others':
				$helper		= new View_Helper_Work_Time_Dashboard_Others( $this->env );
				break;
		}
		$this->respondData( $helper->render() );
	}
}
