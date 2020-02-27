<?php
class Hook_Auth extends CMF_Hydrogen_Hook{

	static public function onAppException( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$payload	= (object) $data;
		if( !property_exists( $payload, 'exception' ) )
			throw new Exception( 'No exception data given' );
		if( !( $payload->exception instanceof Exception ) )
			throw new Exception( 'Given exception data is not an exception object' );
		$request	= $env->getRequest();
		$session	= $env->getSession();
		if( $payload->exception->getCode() == 403 ){
			if( !$session->get( 'userId' ) ){
				$forwardUrl	= $request->get( 'controller' );
				if( $request->get( 'action' ) )
					$forwardUrl	.= '/'.$request->get( 'action' );
				if( $request->get( 'arguments' ) )
					foreach( $request->get( 'arguments' ) as $argument )
						$forwardUrl	.= '/'.$argument;
				$url	= $env->url.'auth/login?from='.$forwardUrl;
				Net_HTTP_Status::sendHeader( 403 );
				if( !$request->isAjax() )
					header( 'Location: '.$url );
				exit;
			}
		}
		return FALSE;
	}

	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$userId		= (int) $env->getSession()->get( 'userId' );														//  get ID of current user (or zero)
		if( $userId ){
			$cookie		= new Net_HTTP_Cookie( parse_url( $env->url, PHP_URL_PATH ) );
			$remember	= (bool) $cookie->get( 'auth_remember' );
			$env->getSession()->set( 'isRemembered', $remember );
			$script		= 'Auth.init('.$userId.','.json_encode( $remember ).');';											//  initialize Auth class with user ID
			$env->getPage()->js->addScriptOnReady( $script, 1 );															//  enlist script to be run on ready
		}
	}
}
