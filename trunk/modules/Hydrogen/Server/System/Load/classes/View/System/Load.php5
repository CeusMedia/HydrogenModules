<?php
class View_System_Load extends CMF_Hydrogen_View{

	public function ajaxRenderIndicator(){
		$load		= $this->getData( 'load' );														//  get load registered by controller
		$cores		= max( 1, $this->env->getConfig()->get( 'module.server_system_load.cores' ) );	//  get number of cpu cores from module config
		$load		= 1 / ( 1 + $load / $cores );													//  calculate load relative to number of cores
		$indicator	= new UI_HTML_Indicator();														//  create instance of indicator renderer
		print( $indicator->build( $load, 1 ) );														//  render and print indicator
		exit;																						//  and quit application
	}
}
?>