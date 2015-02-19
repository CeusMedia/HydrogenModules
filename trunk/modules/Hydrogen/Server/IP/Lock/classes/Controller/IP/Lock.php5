<?php
class Controller_IP_Lock extends CMF_Hydrogen_Controller{

	static public function ___onEnvInit( $env ){
		$ip		= getEnv( 'REMOTE_ADDR' );																	//  get IP address of request
		$logic	= Logic_IP_Lock::getInstance( $env );														//  get instance of IP lock logic
		$logic->unlockIfOverdue( $ip );																		//  clear lock if existing and outdated
		$logic->applyFilters();
		if( $logic->isLockedIp( $ip ) ){																	//  lock is active for IP address
			$lock	= $logic->getByIp( $ip );																//  get lock according to IP address
			$logic->countView( $lock->ipLockId );															//  count this erro page view
			Net_HTTP_Status::sendHeader( $lock->reason->code );												//  send HTTP status code header
			header( 'Content-type: text/html; charset=utf-8' );												//  send MIME type header for UTF-8 HTML error page
			if( $lock->unlockIn > 0 )																		//  seconds to retry after are set
				header( 'Retry-After: '.$lock->unlockIn );													//  send retry header
			$retry		= $lock->unlockIn ? 'in '.$lock->unlockIn.' seconds' : '<em>never</em>';			//  render retry time
			$comment	= '<em>none</em>';																	//  assume empty reason description
			if( $lock->reason->description )																//  reason has description
				$comment	= '<xmp>'.$lock->reason->description.'</xmp>';									//  render reason description
            $message    = '<h1>Access denied</h1><p>'.join( '<br/>', array(									//  assemble HTML content
				'Access from your IP address is blocked.',													//  declare lock down
				'Reason: '.$lock->reason->title,															//  show lock reason title
				'Comment: '.$comment,																		//  show lock reason description
//				'Retry: '.$retry,																			//  show time to retry
			) ).'</p>';																						//  close paragraph
			print( $message );																				//  display error message
			exit;																							//  and quit application
		}
	}
}
