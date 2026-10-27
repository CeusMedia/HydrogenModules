<?php

/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Exception\NotSupported as NotSupportedException;
use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\Mail\Transport\Local as LocalMailTransport;
use CeusMedia\Mail\Transport\SMTP as SmtpMailTransport;

/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@todo		code doc
 */

class Logic_Mail_Transporter extends Logic
{
	protected Logic_Mail $logicMail;

	/**
	 *	@param		bool		$verbose
	 *	@return		object
	 *	@throws		ReflectionException			if mail logic construction failed
	 *	@throws		NotSupportedException		if configured transport type is invalid
	 */
	public function getTransportByConfig( bool $verbose = FALSE ): object
	{
		if( empty( $this->logicMail ) )
			$this->logicMail	= Logic_Mail::getInstance( $this->env );

		/** @var Dictionary $options */
		$options	= $this->env->getConfig()->getAll( 'module.resource_mail.transport.', TRUE );
		$type		= strtolower( $options->get( 'type' ) );
		$type		= in_array( $type, ['local', 'default'], TRUE ) ? 'sendmail' : $type;

		if( !in_array( $type, ['smtp', 'sendmail'], TRUE ) )
			throw new NotSupportedException( 'No valid mail transport configured' );

		return match( $type ){
			'smtp'		=> $this->createSmtpTransport( $options, $verbose ),
			'sendmail'	=> $this->createSendmailTransport(),
		};
	}

	/**
	 *	@return		LocalMailTransport
	 */
	protected function createSendmailTransport(): LocalMailTransport
	{
		return new LocalMailTransport();
	}

	/**
	 *	@param		Dictionary		$options
	 *	@param		bool			$verbose
	 *	@return		SmtpMailTransport
	 */
	protected function createSmtpTransport( Dictionary $options, bool $verbose = FALSE ): SmtpMailTransport
	{
		return SmtpMailTransport::getInstance(
			$options->get( 'hostname' ),
			$options->get( 'port' ),
			$options->get( 'username' ),
			$options->get( 'password' )
		)->setSecure( $options->get( 'secure' ) )->setVerbose( $verbose );
	}

}