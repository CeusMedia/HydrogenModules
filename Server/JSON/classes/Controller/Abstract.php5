<?php
/**
 *	Abstract Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Abstract.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
/**
 *	Abstract Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@extends		CMF_Hydrogen_Controller
 *	@uses			Logic_Chat
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Abstract.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
class Controller_Abstract extends CMF_Hydrogen_Controller {

	protected function logException( Exception $exception ) {
		UI_HTML_Exception_Page::display( $exception );
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
			$mail	= new Mail_Syslog_Exception( $this->env, array( 'exception' => $exception ) );
			$mail->sendTo( (object) $user );
		}
	}

	protected function setupView( $force = TRUE ){}
}
?>
