<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Logic;

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Logic_EventQueue extends Logic
{
	protected Model_Queue_Event $model;
	protected string $scope			= '';
	protected string $origin		= '';
	protected string $userId		= '0';

	/**
	 *	Adds a new event.
	 *	@access		public
	 *	@param		string			$identifier		Identifier of event
	 *	@param		mixed			$data			Data for event handling
	 *	@param		string|NULL		$origin			...
	 *	@return		int|string		ID of new event
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add( string $identifier, mixed $data, ?string $origin = NULL ): int|string
	{
		return $this->model->add( [
			'userId'		=> $this->userId,
			'status'		=> Model_Queue_Event::STATUS_NEW,
			'scope'			=> $this->scope,
			'identifier'	=> $identifier,
			'origin'		=> $origin,
			'data'			=> json_encode( $data ),
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		] );
	}

	/**
	 *	Return number of events matching given conditions.
	 *	If scope is set and not in conditions, it will be added.
	 *	@access		public
	 *	@param		array		$conditions		Map of conditions to match with
	 *	@return		integer
	 */
	public function count( array $conditions = [] ): int
	{
		if( !$this->scope && !array_key_exists( 'scope', $conditions ) )
			$conditions['scope']	= $this->scope;
		return $this->model->count( $conditions );
	}

	/**
	 *	Return event by given ID.
	 *	@access		public
	 *	@param		int|string		$eventId		ID of event to return
	 *	@return		object|NULL		Data object of event
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function get( int|string $eventId ): object|NULL
	{
		return $this->model->get( $eventId );
	}

	/**
	 *	Returns first event matching given conditions.
	 *	If scope is set and not in conditions, it will be added.
	 *	@access		public
	 *	@param		array		$conditions		Map of conditions to match with
	 *	@return		array
	 */
	public function getByConditions( array $conditions = [] ): array
	{
		if( !$this->scope && !array_key_exists( 'scope', $conditions ) )
			$conditions['scope']	= $this->scope;
		return $this->model->getByIndices( $conditions, ['createdAt' => 'ASC'] );
	}

	/**
	 *	Sets event status to "new".
	 *	@access		public
	 *	@param		int|string		$eventId		ID of event to mark as new
	 *	@param		mixed			$result			Results to store
	 *	@return		static
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function markAsNew( int|string $eventId, mixed $result = NULL ): static
	{
		return $this->setStatus( $eventId, Model_Queue_Event::STATUS_NEW, $result );
	}

	/**
	 *	Sets event status to "ignored".
	 *	@access		public
	 *	@param		int|string		$eventId		ID of event to mark as ignored
	 *	@param		mixed			$result			Results to store
	 *	@return		static
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function markAsIgnored( int|string $eventId, mixed $result = NULL ): static
	{
		return $this->setStatus( $eventId, Model_Queue_Event::STATUS_IGNORED, $result );
	}

	/**
	 *	Sets event status to "revoked".
	 *	@access		public
	 *	@param		int|string		$eventId		ID of event to mark as revoked
	 *	@param		mixed			$result			Results to store
	 *	@return		static
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function markAsRevoked( int|string $eventId, mixed $result = NULL ): static
	{
		return $this->setStatus( $eventId, Model_Queue_Event::STATUS_REVOKED, $result );
	}

	/**
	 *	Sets event status to "running".
	 *	@access		public
	 *	@param		int|string		$eventId		ID of event to mark as running
	 *	@param		mixed			$result			Results to store
	 *	@return		self
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function markAsInRunning( int|string $eventId, mixed $result = NULL ): static
	{
		return $this->setStatus( $eventId, Model_Queue_Event::STATUS_RUNNING, $result );
	}

	/**
	 *	Sets event status to "failed".
	 *	@access		public
	 *	@param		int|string		$eventId		ID of event to mark as failed
	 *	@param		mixed			$result			Results to store
	 *	@return		static
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function markAsFailed( int|string $eventId, mixed $result = NULL ): static
	{
		return $this->setStatus( $eventId, Model_Queue_Event::STATUS_FAILED, $result );
	}

	/**
	 *	Sets event status to "succeeded".
	 *	@access		public
	 *	@param		int|string		$eventId		ID of event to mark as succeeded
	 *	@param		mixed			$result			Results to store
	 *	@return		static
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function markAsSucceeded( int|string $eventId, mixed $result = NULL ): static
	{
		return $this->setStatus( $eventId, Model_Queue_Event::STATUS_SUCCEEDED, $result );
	}

	/**
	 *	Sets scope.
	 *	@access		public
	 *	@param		string		$scope			Scope to set
	 *	@return		static
	 */
	public function setScope( string $scope ): static
	{
		$this->scope	= $scope;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->model	= new Model_Queue_Event( $this->env );
	}

	/**
	 *	@param		int|string		$eventId
	 *	@param		int				$status
	 *	@param		mixed			$result
	 *	@return		static
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function setStatus( int|string $eventId, int $status, mixed $result = NULL ): static
	{
		$event		= $this->get( $eventId );
		if( NULL === $event )
			throw new DomainException( 'Invalid event ID' );

		$possibleStatuses	= Model_Queue_Event::STATUSES_TRANSITIONS[$event->status];
		if( !in_array( $status, $possibleStatuses, TRUE ) )
			throw new RangeException( 'Invalid status transition' );

		$this->model->edit( $eventId, [
			'status'		=> $status,
			'result'		=> json_encode( $result ),
			'modifiedAt'	=> time(),
		] );
		return $this;
	}
}
