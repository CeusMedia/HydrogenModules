<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Database_Lock extends Logic
{
	protected $model;

	/**
	 *	@deprecated		use hook class instead
	 *	@todo			remove after all installations are updated
	 */
	public static function ___onAutoModuleLockRelease( Environment $env, $context/*, $module, $data = []*/ )
	{
		$request	= $env->getRequest();
		if( $request->isAjax() )
			return FALSE;
//		error_log( time().": ".json_encode( $request->getAll() )."\n", 3, "unlock.log" );
		$payload	= array(
			'userId'		=> $env->getSession()->get( 'auth_user_id' ),
			'request'		=> $request,
			'controller'	=> $request->get( '__controller' ),
			'action'		=> $request->get( '__action' ),
			'uri'			=> getEnv( 'REQUEST_URI' ),
		);
		return $env->getModules()->callHookWithPayload( 'Database_Lock', 'checkRelease', $context, $payload );
	}

	public static function release( Environment $env, $subject, $entryId = NULL )
	{
		$lock	= new self( $env );
		$lock->unlock( $subject, $entryId );
	}

	public function getLock( $subject, $entryId )
	{
		$lock	= $this->model->getByIndices( array(
			'subject'	=> $subject,
			'entryId'	=> $entryId,
		) );
		if( !$lock )
			throw new RuntimeException( 'Resource is not locked' );
		return $lock;
	}

	public function getLockUser( $subject, $entryId )
	{
		$lockUserId	= $this->getLockUserId( $subject, $entryId );
		$modelUser	= new Model_User( $this->env );
		$lockUser	= $modelUser->get( $lockUserId );
		if( !$lockUser )
			throw new RuntimeException( 'Lock user is not existing anymore' );
		return $lockUser;
	}

	public function getLockUserId( $subject, $entryId )
	{
		$lock	= $this->getLock( $subject, $entryId );
		return $lock->userId;
	}

	public function getUserLocks( $userId )
	{
		return $this->model->getAllByIndex( 'userId', $userId );
	}

	public function getSubjectLocks( $subject )
	{
		return $this->model->getAllByIndex( 'subject', $subject );
	}

	public function isLocked( $subject, $entryId, $userId = NULL )
	{
		$indices	= array( 'subject' => $subject );
		if( $entryId )
			$indices['entryId']	= (int) $entryId;
		if( $userId )
			$indices['userId']	= $userId;
		return (bool) $this->model->countByIndices( $indices );
	}

	public function isLockedByMe( $subject, $entryId )
	{
		return $this->isLocked( $subject, $entryId, $this->userId );
	}

	public function isLockedByOther( $subject, $entryId )
	{
		return $this->isLocked( $subject, $entryId, '!= '.$this->userId );
	}

	public function lock( $subject, $entryId, $userId )
	{
		if( $this->isLocked( $subject, $entryId, $userId ) )
			return NULL;
		if( $this->isLocked( $subject, $entryId, '!= '.$userId ) )
			return FALSE;
		$this->model->add( array(
			'userId'	=> (int) $userId,
			'subject'	=> $subject,
			'entryId'	=> (int) $entryId,
			'timestamp'	=> time(),
		) );
		return TRUE;
	}

	public function lockByMe( $subject, $entryId )
	{
		return $this->lock( $subject, $entryId, $this->userId );
	}

	public function unlock( $subject, $entryId = 0, $userId = NULL )
	{
		$userId		= $userId !== NULL ? (int) $userId : $this->userId;				//  insert current userId of none given
		if( !$this->isLocked( $subject, $entryId, $userId ) )
			return FALSE;
		$indices	= array( 'subject' => $subject );
		if( $entryId )
			$indices['entryId']	= (int) $entryId;
		if( $userId )
			$indices['userId']	= $userId;
		return (bool) $this->model->removeByIndices( $indices );
	}

	protected function __onInit()
	{
		$this->model	= new Model_Lock( $this->env );
		$this->userId	= (int) $this->env->getSession()->get( 'auth_user_id' );
	}
}
