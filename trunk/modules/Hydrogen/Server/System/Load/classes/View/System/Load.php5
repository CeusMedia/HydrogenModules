<?php
class View_System_Load extends CMF_Hydrogen_View{

	public function ajaxRenderIndicator(){
		$load		= $this->getData( 'load' );
		$cores		= $this->env->getConfig()->get( 'module.server_system_load.cores' );
		$load		= 1 / ( 1 + $load / $cores );
		$indicator	= new UI_HTML_Indicator();
		print( $indicator->build( $load, 1 ) );
		exit;
	}
}
?>