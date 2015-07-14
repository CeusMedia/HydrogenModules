<?php
/**
 *	Types:
 *	0	- Promotion
 *	1	- Invitation
 *
 *	States:
 *	-2	- cancelled
 *	-1	- outdated
 *	0	- new (used on invite mode)
 *	1	- sent
 *	2	- used
 */
class Model_User_Invite extends CMF_Hydrogen_Model {

	protected $name			= 'user_invites';
	protected $columns		= array(
		'userInviteId',
		'inviterId',
		'invitedId',
		'projectId',
		'type',
		'status',
		'code',
		'email',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'userInviteId';
	protected $indices		= array(
		'inviterId',
		'invitedId',
		'projectId',
		'type',
		'status',
		'email',
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

	public function getInviteByEmail( $email ){
		$model	= new Model_User_Invite( $this->env );
		return $model->getByIndex( 'email', $email );
	}

	public function setStatus( $userInviteId, $status ){
		if( !is_int( $status ) ) 
			throw new InvalidArgumentException( 'Status must be integer' );
		if( $status < -2 || $status > 2 ) 
			throw new RangeException( 'Status must be within -2 and 2' );
		$model	= new Model_User_Invite( $this->env );
		return $model->edit( $userInviteId, array( 'status' => $status, 'modifiedAt' => time() ) );	//  set new status and note timestamp
	}
}
?>