<?php

use CeusMedia\Common\ADT\JSON\Parser as JsonParser;
use CeusMedia\HydrogenFramework\Controller\Ajax as Controller;

class Controller_Ajax_Manage_Form extends Controller
{
	/**
	 *	@param		string		$formId
	 *	@param		string		$tabId
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	public function setTab( string $formId, string $tabId ): void
	{
		$this->session->set( 'manage_forms_tab', $tabId );
		$this->respondData( 'ok' );
	}

	/**
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
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
			'userId'	=> Logic_Authentication::getInstance( $this->env )->getCurrentUserId(),
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
