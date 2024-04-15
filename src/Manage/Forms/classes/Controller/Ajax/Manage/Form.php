<?php

use CeusMedia\Common\ADT\JSON\Parser as JsonParser;
use CeusMedia\HydrogenFramework\Controller\Ajax as Controller;

class View_Ajax_Manage_Form extends Controller
{
	/**
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function testTransferRules(): void
	{
		if( !$this->request->getMethod()->isPost() )
			$this->respondError( 1, 'Must be a POST request' );

		$ruleId	= $this->request->get( 'ruleId' );
		if( 0 === ((int) $ruleId) )
			$this->respondError( 2, 'No import rule ID given' );

		$rules	= $this->request->get( 'rules' );

		$response	= [
			'userId'	=> $this->session->get( 'auth_user_id' ),
			'ruleId'	=> $ruleId,
			'rules'		=> $rules,
			'status'	=> 'empty',
			'message'	=> NULL,
		];

		if( 0 !== strlen( trim( $rules ) ) ){
			$parser	= new JsonParser;
			try{
				$parser->parse( $rules );
				$response['status']	= 'parsed';
			}
			catch( RuntimeException $e ){
				$response['status']		= 'exception';
				$response['message']	= $e->getMessage();
			}
		}
		$this->respondData( $response );
	}
}