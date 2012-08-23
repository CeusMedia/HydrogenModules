<?php
class View_Helper_JsonServerResponseCodeHandler extends CMF_Hydrogen_View_Helper_Abstract{

	public static $labelUnknownIdentifier	= 'unknown';
	
	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
	}

	public static function handle( $env, $code, $identifier = NULL ){
		$helper	= new View_Helper_JsonServerResponseCodeHandler( $env );
		return $helper->handleCode( $code, $identifier );
	}
	
	public function handleCode( $code, $identifier = NULL ){
		$controller	= $this->env->getRequest()->get( 'controller' );
		$action		= $this->env->getRequest()->get( 'action' );
		$messenger	= $this->env->getMessenger();
		try{
			$words		= $this->env->getLanguage()->getWords( $controller );
			if( !empty( $words[$action] ) ){
				$messages	= $words[$action];
				if( isset( $messages['msgSuccess'.$code] ) )
					return $messenger->noteSuccess( $messages['msgSuccess'.$code], $identifier );
				if( isset( $messages['msgNotice'.$code] ) )
					return $messenger->noteNotice( $messages['msgNotice'.$code], $identifier );
				if( isset( $messages['msgError'.$code] ) )
					return $messenger->noteError( $messages['msgError'.$code], $identifier );
				if( isset( $messages['msgFailure'.$code] ) )
					return $messenger->noteFailure( $messages['msgFailure'.$code], $identifier );
				if( $code > 0 ){
					if( isset( $messages['msgSuccess'] ) )
						return $messenger->noteSuccess( $messages['msgSuccess'], $identifier );
				}
				else{
					if( isset( $messages['msgNotice'] ) )
						return $messenger->noteNotice( $messages['msgNotice'], $code, $identifier );
					if( isset( $messages['msgError'] ) )
						return $messenger->noteError( $messages['msgError'], $code, $identifier );
					if( isset( $messages['msgFailure'] ) )
						return $messenger->noteFailure( $messages['msgFailure'], $code, $identifier );
				}
			}
		}
		catch( Exception $e ){}
		try{
			$words		= $this->env->getLanguage()->getWords( 'main' );
			$identifier	= $identifier ? $identifier : self::$labelUnknownIdentifier;
			if( !empty( $words['messages'] ) ){
				$messages	= $words['messages'];
				if( isset( $messages['notice'.$code] ) )
					return $messenger->noteNotice( $messages['notice'.$code], $identifier, $controller, $action );
				if( isset( $messages['error'.$code] ) )
					return $messenger->noteError( $messages['error'.$code], $identifier, $controller, $action );
				if( isset( $messages['failure'.$code] ) )
					return $messenger->noteFailure( $messages['failure'.$code], $identifier, $controller, $action );
				if( $code > 0 ){
					if( isset( $messages['success'] ) )
						return $messenger->noteSuccess( $messages['success'], $identifier );
				}
				else{
					if( isset( $messages['error'] ) )
						return $messenger->noteError( $messages['error'], $code, $identifier, $controller, $action );
					if( isset( $messages['failure'] ) )
						return $messenger->noteFailure( $messages['failure'], $code, $identifier, $controller, $action );
				}
			}
		}
		catch( Exception $e ){}
		if( $code < 1 )
			return $messenger->noteFailure( 'Unexpected error while calling action "'.$action.'" in controller "'.$controller.'".');
	}
}
?>