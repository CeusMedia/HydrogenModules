<?php
class Model_User_Invite extends CMF_Hydrogen_Model {

	protected $name			= 'user_invites';
	protected $columns		= array(
		'userInviteId',
		'inviterId',
		'invitedId',
		'status',
		'code',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'userInviteId';
	protected $indices		= array(
		'inviterId',
		'invitedId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function generateInviteCode( $inviterId, $mode = 0, $length = 10, $split = 5 ){
		switch( $mode ){
			default:
				$seed	= uniqid( $inviterId.'-'.microtime( TRUE ), TRUE );
				$code	= md5( $userId.$seed );
		}
		$length	= min( $length, strlen( $code ) );													//  length cannot be longer than generated raw code
		$length	= max( $length, 3 );																//  length must be atleast 3
		$split	= min( $split, $length );															//  split length cannot be longer than length
		$split	= max( $split, 0 );																	//  split cannot by negative
		
		$pos	= rand( 0, count( $code ) - $length - 1 );
		$code	= substr( $code, $pos, $length );
		$code	= strtoupper( $code );
		if( $split !== 0 && $split !== $length )													//  valid split length is set
			$code	= join( '-', str_split( $code, $split ) );
		return $code;
	}
}
?>