<?php
class View_Tool_Calculator extends CMF_Hydrogen_View{

	static public function ___onPageBuild( CMF_Hydrogen_Environment $env, $module, $context, $data = array() ){
		$data	= (object) $data;
		$helper	= new View_Helper_Tool_Calculator( $env );
		$helper->setId( 'calc-modal' );
		$env->getPage()->js->addScriptOnReady( 'prepareCalculatorLink();' );
		$data->content	.= '
<div id="modalCalculator" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	'.$helper->render().'
</div>';
	}

	public function index(){}
}
?>
