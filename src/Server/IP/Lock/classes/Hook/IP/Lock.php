<?php

use CeusMedia\Common\Net\HTTP\Status as HttpStatus;
use CeusMedia\HydrogenFramework\Hook;

class Hook_IP_Lock extends Hook
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onEnvInit(): void
	{
		$ip		= getEnv( 'REMOTE_ADDR' );															//  get IP address of request
		$logic	= Logic_IP_Lock::getInstance( $this->env );													//  get instance of IP lock logic
		$logic->unlockIfOverdue( $ip, FALSE );														//  clear lock if existing and outdated
		$logic->applyFilters();																				//  apply filters on request of IP
		if( !$logic->isLockedIp( $ip ) )																	//  no lock is active for IP address
			return;
		$lock	= $logic->getByIp( $ip );																	//  get lock according to IP address
		$logic->countView( $lock );																//  count this error page view
		HttpStatus::sendHeader( $lock->reason->code );														//  send HTTP status code header
		header( 'Content-type: text/html; charset=utf-8' );													//  send MIME type header for UTF-8 HTML error page
		if( $lock->unlockIn > 0 )																			//  seconds to retry after are set
			header( 'Retry-After: '.$lock->unlockIn );														//  send retry header
		$w	= [																							//  default texts, if language is not available yet
			'heading'		=> 'Access denied',
			'textBlocked'	=> 'Access from your IP address is blocked.',
			'labelReason'	=> 'Reason:',
			'labelComment'	=> 'Comment:',
			'labelRetry'	=> 'Retry:',
			'valueRetryIn'	=> 'in %d seconds',
			'valueNever'	=> 'never',
			'valueNone'		=> 'none',
		];
		try{
			$w	= array_merge( $w, $this->env->getLanguage()->getSection( 'ip/lock', 'blocked' ) );	//  load localized texts
		}
		catch( Throwable ){}
		$retry		= $lock->unlockIn ? sprintf( $w['valueRetryIn'], $lock->unlockIn ) : '<em>'.$w['valueNever'].'</em>';	//  render retry time
		$comment	= '<em>'.$w['valueNone'].'</em>';														//  assume empty reason description
		if( $lock->reason->description )																	//  reason has description
			$comment	= '<xmp>'.$lock->reason->description.'</xmp>';										//  render reason description
		$message	= '<h1>'.$w['heading'].'</h1><p>'.join( '<br/>', [									//  assemble HTML content
			$w['textBlocked'],																				//  declare lock down
			$w['labelReason'].' '.$lock->reason->title,														//  show lock reason title
			$w['labelComment'].' '.$comment,																//  show lock reason description
//			$w['labelRetry'].' '.$retry,																	//  show time to retry
		] ).'</p>';																							//  close paragraph
		print( $message );																					//  display error message
		exit;																								//  and quit application
	}
}
