<?php
class Controller_Ajax_Helper_Markdown extends CMF_Hydrogen_Controller_Ajax
{
	public function render()
	{
		$request	= $this->env->getRequest();											//  shortcut request
		$content	= $request->get( 'content' );										//  get given Markdown content
		$content	= html_entity_decode( $content );									//  ...
		$html		= View_Helper_Markdown::transformStatic( $this->env, $content );	//  convert Markdown to HTML
		$client		= $request->getHeadersByName( 'X-Hydrogen-Client', TRUE );			//  get client header if available
		if( $client && $client->getValue() === "AJAJ" ){								//  ...
			$response	= array( 'status' => 'ok', 'data' => $html );					//  collect data to return
			$this->respondData( $response );											//  respond JSON data and quit
		}
		$this->respond( $html );														//  otherwise respond HTML
	}
}
