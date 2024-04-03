<?php

use CeusMedia\HydrogenFramework\Model;

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
class Model_User_Invite extends Model
{
	protected string $name			= 'user_invites';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'userInviteId';

	protected array $indices		= [
		'inviterId',
		'invitedId',
		'projectId',
		'type',
		'status',
		'email',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;

	public function generateInviteCode( $inviterId, $mode = 0, int $length = 10, int $split = 5 )
	{
		switch( $mode ){
			default:
				$seed	= uniqid( $inviterId.'-'.microtime( TRUE ), TRUE );
				$code	= md5( $userId.$seed );
		}
		$length	= min( $length, strlen( $code ) );													//  length cannot be longer than generated raw code
		$length	= max( $length, 3 );																//  length must be at least 3
		$split	= min( $split, $length );															//  split length cannot be longer than length
		$split	= max( $split, 0 );																	//  split cannot by negative

		$pos	= random_int( 0, count( $code ) - $length - 1 );
		$code	= substr( $code, $pos, $length );
		$code	= strtoupper( $code );
		if( $split !== 0 && $split !== $length )													//  valid split length is set
			$code	= join( '-', str_split( $code, $split ) );
		return $code;
	}

	public function getInviteByEmail( $email )
	{
		$model	= new Model_User_Invite( $this->env );
		return $model->getByIndex( 'email', $email );
	}

	public function setStatus( $userInviteId, $status ): int
	{
		if( !is_int( $status ) )
			throw new InvalidArgumentException( 'Status must be integer' );
		if( $status < -2 || $status > 2 )
			throw new RangeException( 'Status must be within -2 and 2' );
		$model	= new Model_User_Invite( $this->env );
		return $model->edit( $userInviteId, array( 'status' => $status, 'modifiedAt' => time() ) );	//  set new status and note timestamp
	}
}
