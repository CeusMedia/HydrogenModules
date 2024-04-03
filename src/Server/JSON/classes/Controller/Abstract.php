<?php
/**
 *	Abstract Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\HydrogenFramework\Controller;

/**
 *	Abstract Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class Controller_Abstract extends Controller
{
	protected function logException( Exception $exception )
	{
		HtmlExceptionPage::display( $exception );
		die;

		if( $this->env->getModules()->has( 'Server_Syslog' ) ){
			$fileName	= $this->env->getConfig()->get( 'log.exception' );
			$serial		= $exception->getMessage();
			try{
				$serial		= serialize( $exception );
				error_log( time().":".base64_encode( $serial )."\n", 3, './logs/exception.log' );
			}
			catch( Exception $e ){}
			$user	= array( 'email' => $this->env->getConfig()->get( 'app.email.developer' ) );
			$mail	= new Mail_Syslog_Exception( $this->env, ['exception' => $exception] );
			$mail->sendTo( (object) $user );
		}
	}

	protected function setupView( bool $force = TRUE ): self
	{
		return $this;
	}
}
