<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Helper_Markdown extends AjaxController
{
	public function render()
	{
		$request	= $this->env->getRequest();											//  shortcut request
		$content	= $request->get( 'content' );										//  get given Markdown content
		$content	= html_entity_decode( $content );									//  ...
		$html		= View_Helper_Markdown::transformStatic( $this->env, $content );	//  convert Markdown to HTML
		$client		= $request->getHeadersByName( 'X-Hydrogen-Client', TRUE );			//  get client header if available
		if( $client && $client->getValue() === "AJAJ" ){								//  ...
			$response	= ['status' => 'ok', 'data' => $html];					//  collect data to return
			$this->respondData( $response );											//  respond JSON data and quit
		}
		$this->respondData( $html );													//  otherwise respond HTML
	}
}
