<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Database_Lock extends Logic
{
	protected Model_Lock $model;
	protected string $userId;

	public static function release( Environment $env, $subject, $entryId = NULL ): void
	{
		$lock	= new self( $env );
		$lock->unlock( $subject, $entryId );
	}

	public function getLock( string $subject, string $entryId ): ?object
	{
		$lock	= $this->model->getByIndices( [
			'subject'	=> $subject,
			'entryId'	=> $entryId,
		] );
		if( NULL === $lock )
			throw new RuntimeException( 'Resource is not locked' );
		return $lock;
	}

	public function getLockUser( string $subject, string $entryId ): object
	{
		$lockUserId	= $this->getLockUserId( $subject, $entryId );
		$modelUser	= new Model_User( $this->env );
		$lockUser	= $modelUser->get( $lockUserId );
		if( !$lockUser )
			throw new RuntimeException( 'Lock user is not existing anymore' );
		return $lockUser;
	}

	public function getLockUserId( string $subject, string $entryId ): string
	{
		$lock	= $this->getLock( $subject, $entryId );
		return $lock->userId;
	}

	public function getUserLocks( string $userId ): array
	{
		return $this->model->getAllByIndex( 'userId', $userId );
	}

	public function getSubjectLocks( string $subject ): array
	{
		return $this->model->getAllByIndex( 'subject', $subject );
	}

	public function isLocked( string $subject, string $entryId, ?string $userId = NULL ): bool
	{
		$indices	= ['subject' => $subject];
		if( $entryId )
			$indices['entryId']	= (int) $entryId;
		if( $userId )
			$indices['userId']	= $userId;
		return (bool) $this->model->countByIndices( $indices );
	}

	public function isLockedByMe( string $subject, string $entryId ): bool
	{
		return $this->isLocked( $subject, $entryId, $this->userId );
	}

	public function isLockedByOther( string $subject, string $entryId ): bool
	{
		return $this->isLocked( $subject, $entryId, '!= '.$this->userId );
	}

	public function lock( string $subject, string $entryId, string $userId ): ?bool
	{
		if( $this->isLocked( $subject, $entryId, $userId ) )
			return NULL;
		if( $this->isLocked( $subject, $entryId, '!= '.$userId ) )
			return FALSE;
		$this->model->add( [
			'userId'	=> (int) $userId,
			'subject'	=> $subject,
			'entryId'	=> (int) $entryId,
			'timestamp'	=> time(),
		] );
		return TRUE;
	}

	public function lockByMe( string $subject, string $entryId ): ?bool
	{
		return $this->lock( $subject, $entryId, $this->userId );
	}

	public function unlock( string $subject, string $entryId = '0', ?string $userId = NULL ): bool
	{
		$userId		= $userId ?? $this->userId;									//  insert current userId of none given
		if( !$this->isLocked( $subject, $entryId, $userId ) )
			return FALSE;
		$indices	= ['subject' => $subject];
		if( $entryId )
			$indices['entryId']	= (int) $entryId;
		if( $userId )
			$indices['userId']	= $userId;
		return (bool) $this->model->removeByIndices( $indices );
	}

	protected function __onInit(): void
	{
		$this->model	= new Model_Lock( $this->env );
		$this->userId	= $this->env->getSession()->get( 'auth_user_id' );
	}
}
