<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_JsonServerResponseCodeHandler extends Abstraction
{
	public static string $labelUnknownIdentifier	= 'unknown';

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public static function handle( Environment $env, $code, $identifier = NULL ): void
	{
		$helper	= new View_Helper_JsonServerResponseCodeHandler( $env );
		$helper->handleCode( $code, $identifier );
	}

	public function handleCode( $code, $identifier = NULL ): void
	{
		$controller	= $this->env->getRequest()->get( '__controller' );
		$action		= $this->env->getRequest()->get( '__action' );
		$messenger	= $this->env->getMessenger();
		try{
			$words		= $this->env->getLanguage()->getWords( $controller );
			if( !empty( $words[$action] ) ){
				$messages	= $words[$action];
				if( isset( $messages['msgSuccess'.$code] ) ){
					$messenger?->noteSuccess( $messages['msgSuccess'.$code], $identifier );
					return;
				}
				if( isset( $messages['msgNotice'.$code] ) ){
					$messenger?->noteNotice( $messages['msgNotice'.$code], $identifier );
					return;
				}
				if( isset( $messages['msgError'.$code] ) ){
					$messenger?->noteError( $messages['msgError'.$code], $identifier );
					return;
				}
				if( isset( $messages['msgFailure'.$code] ) ){
					$messenger?->noteFailure( $messages['msgFailure'.$code], $identifier );
					return;
				}
				if( $code > 0 ){
					if( isset( $messages['msgSuccess'] ) ) {
						$messenger->noteSuccess($messages['msgSuccess'], $identifier);
						return;
					}
				}
				else{
					if( isset( $messages['msgNotice'] ) ){
						$messenger->noteNotice( $messages['msgNotice'], $code, $identifier );
						return;
					}
					if( isset( $messages['msgError'] ) ){
						$messenger->noteError( $messages['msgError'], $code, $identifier );
						return;
					}
					if( isset( $messages['msgFailure'] ) ){
						$messenger->noteFailure( $messages['msgFailure'], $code, $identifier );
						return;
					}
				}
			}
		}
		catch( Exception ){}
		try{
			$words		= $this->env->getLanguage()->getWords( 'main' );
			$identifier	= $identifier ?: self::$labelUnknownIdentifier;
			if( !empty( $words['messages'] ) ){
				$messages	= $words['messages'];
				if( isset( $messages['notice'.$code] ) ){
					$messenger->noteNotice( $messages['notice'.$code], $identifier, $controller, $action );
					return;
				}
				if( isset( $messages['error'.$code] ) ){
					$messenger->noteError( $messages['error'.$code], $identifier, $controller, $action );
					return;
				}
				if( isset( $messages['failure'.$code] ) ){
					$messenger->noteFailure( $messages['failure'.$code], $identifier, $controller, $action );
					return;
				}
				if( $code > 0 ){
					if( isset( $messages['success'] ) ){
						$messenger->noteSuccess( $messages['success'], $identifier );
						return;
					}
				}
				else{
					if( isset( $messages['error'] ) ){
						$messenger->noteError( $messages['error'], $code, $identifier, $controller, $action );
						return;
					}
					if( isset( $messages['failure'] ) ){
						$messenger->noteFailure( $messages['failure'], $code, $identifier, $controller, $action );
						return;
					}
				}
			}
		}
		catch( Exception ){}
		if( $code < 1 ){
			$messenger->noteFailure( 'Unexpected error while calling action "'.$action.'" in controller "'.$controller.'".');
		}
	}
}
