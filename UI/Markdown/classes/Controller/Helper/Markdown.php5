<?php
class Controller_Helper_AJAJ{

}

class Controller_Helper_Markdown extends CMF_Hydrogen_Controller{

	public function ajaxRender(){
		$request	= $this->env->getRequest();
		$content	= $request->get( 'content' );
		$content	= html_entity_decode( $content );
		$html		= View_Helper_Markdown::transformStatic( $this->env, $content );

		if( $request->isAjax() ){
			$client	= $request->getHeadersByName( 'X-Hydrogen-Client', TRUE );
			if( $client && $client->getValue() === "AJAJ" ){
				$response	= array( 'status' => 'ok', 'data' => $html );
				print( json_encode( $response ) );
			}
			else
				print( $html );
			exit;
		}
	}
}
?>
