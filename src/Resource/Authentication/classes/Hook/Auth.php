<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Net\HTTP\Cookie as HttpCookie;
use CeusMedia\Common\Net\HTTP\Status as HttpStatus;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Auth extends Hook
{
	public function onAppException()
	{
		$load	= (object) $this->payload;
		if( !property_exists( $load, 'exception' ) )
			throw new Exception( 'No exception data given' );
		if( !( $load->exception instanceof Exception ) )
			throw new Exception( 'Given exception data is not an exception object' );
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		if( $load->exception->getCode() == 403 ){
			if( !$session->get( 'auth_user_id' ) ){
				$forwardUrl	= $request->get( '__controller' );
				if( $request->get( '__action' ) )
					$forwardUrl	.= '/'.$request->get( '__action' );
				if( $request->get( '__arguments' ) )
					foreach( $request->get( '__arguments' ) as $argument )
						$forwardUrl	.= '/'.$argument;
				$url	= $this->env->url.'auth/login?from='.$forwardUrl;
				HttpStatus::sendHeader( 403 );
				if( !$request->isAjax() )
					header( 'Location: '.$url );
				exit;
			}
		}
		return FALSE;
	}

	public function onPageApplyModules()
	{
		$session	= $this->env->getSession();
		$userId		= (int) $session->get( 'auth_user_id' );										//  get ID of current user (or zero)
		if( $userId ){
			$cookie		= new HttpCookie( parse_url( $this->env->url, PHP_URL_PATH ) );
			$remember	= (bool) $cookie->get( 'auth_remember' );
			$session->set( 'isRemembered', $remember );
			$script		= 'Auth.init('.$userId.','.json_encode( $remember ).');';					//  initialize Auth class with user ID
			$this->env->getPage()->js->addScriptOnReady( $script, 1 );								//  enlist script to be run on ready
		}
	}

	public function onEnvInitAcl(): bool
	{
//		$this->payload['className']	= '\\CeusMedia\\HydrogenFramework\\Environment\\Resource\\Acl\\Database';
		$this->payload['className']	= 'Resource_Acl_Authentication';
		return TRUE;
	}
}
