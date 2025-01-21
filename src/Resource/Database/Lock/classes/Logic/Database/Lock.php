<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Database_Lock extends Logic
{
	protected Model_Lock $model;

	protected int|string $userId	= 0;

	/**
	 *	@param		Environment			$env
	 *	@param		string				$subject
	 *	@param		int|string|NULL		$entryId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public static function release( Environment $env, string $subject, int|string|NULL $entryId = NULL ): void
	{
		$lock	= new self( $env );
		$lock->unlock( $subject, $entryId );
	}

	/**
	 *	@param		string		$subject
	 *	@param		int|string	$entryId
	 *	@return		?Entity_Database_Lock
	 */
	public function getLock( string $subject, int|string $entryId ): ?Entity_Database_Lock
	{
		/** @var ?Entity_Database_Lock $lock */
		$lock	= $this->model->getByIndices( [
			'subject'	=> $subject,
			'entryId'	=> $entryId,
		] );
		if( NULL === $lock )
			throw new RuntimeException( 'Resource is not locked' );
		return $lock;
	}

	/**
	 *	@param		string			$subject
	 *	@param		int|string		$entryId
	 *	@return		Entity_Database_Lock
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getLockUser( string $subject, int|string $entryId ): Entity_Database_Lock
	{
		$lockUserId	= $this->getLockUserId( $subject, $entryId );
		$modelUser	= new Model_User( $this->env );
		/** @var ?Entity_Database_Lock $lockUser */
		$lockUser	= $modelUser->get( $lockUserId );
		if( NULL === $lockUser )
			throw new RuntimeException( 'Lock user is not existing anymore' );
		return $lockUser;
	}

	/**
	 *	@param		int|string		$subject
	 *	@param		int|string		$entryId
	 *	@return		string
	 */
	public function getLockUserId( int|string $subject, int|string $entryId ): string
	{
		$lock	= $this->getLock( $subject, $entryId );
		return $lock->userId;
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		array
	 */
	public function getUserLocks( int|string $userId ): array
	{
		return $this->model->getAllByIndex( 'userId', $userId );
	}

	/**
	 *	@param		string		$subject
	 *	@return		array
	 */
	public function getSubjectLocks( string $subject ): array
	{
		return $this->model->getAllByIndex( 'subject', $subject );
	}

	/**
	 *	@param		string			$subject
	 *	@param		int|string		$entryId
	 *	@param		int|string|NULL	$userId
	 *	@return		bool
	 */
	public function isLocked( string $subject, int|string $entryId, int|string|NULL $userId = NULL ): bool
	{
		$indices	= ['subject' => $subject];
		if( $entryId )
			$indices['entryId']	= (int) $entryId;
		if( $userId )
			$indices['userId']	= $userId;
		return (bool) $this->model->countByIndices( $indices );
	}

	/**
	 *	@param		string			$subject
	 *	@param		int|string		$entryId
	 *	@return		bool
	 */
	public function isLockedByMe( string $subject, int|string $entryId ): bool
	{
		return $this->isLocked( $subject, $entryId, $this->userId );
	}

	/**
	 *	@param		string			$subject
	 *	@param		int|string		$entryId
	 *	@return		bool
	 */
	public function isLockedByOther( string $subject, int|string $entryId ): bool
	{
		return $this->isLocked( $subject, $entryId, '!= '.$this->userId );
	}

	/**
	 *	@param		string			$subject
	 *	@param		int|string		$entryId
	 *	@param		int|string		$userId
	 *	@return		bool|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function lock( string $subject, int|string $entryId, int|string $userId ): ?bool
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

	/**
	 *	@param		string			$subject
	 *	@param		int|string		$entryId
	 *	@return		bool|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function lockByMe( string $subject, int|string $entryId ): ?bool
	{
		return $this->lock( $subject, $entryId, $this->userId );
	}

	/**
	 *	@param		string			$subject
	 *	@param		int|string		$entryId
	 *	@param		int|string|NULL	$userId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function unlock( string $subject, int|string $entryId = '0', int|string|NULL $userId = NULL ): bool
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

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->model	= new Model_Lock( $this->env );
		$this->userId	= $this->env->getSession()->get( 'auth_user_id', '' );
	}
}
