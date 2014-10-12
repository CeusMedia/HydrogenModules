<?php
class Controller_Helper_Markdown extends CMF_Hydrogen_Controller{

	public function ajaxRender(){
		$content	= $this->env->getRequest()->get( 'content' );
		$html		= View_Helper_Markdown::transformStatic( $this->env, $content );
		print( $html );
		exit;
	}

}
?>
