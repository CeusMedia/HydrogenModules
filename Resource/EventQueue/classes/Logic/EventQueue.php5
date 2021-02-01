<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	...
 *	@extends		CMF_Hydrogen_Logic
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Logic_EventQueue extends CMF_Hydrogen_Logic
{
	protected $model;
	protected $scope;
	protected $origin;
	protected $userId		= 0;

	/**
	 *	Adds a new event.
	 *	@access		public
	 *	@param		string		$identifier		Identifier of event
	 *	@param		mixed		$data			Data for event handling
	 *	@param		string		$origin			...
	 *	@return		string		ID of new event
	 */
	public function add( $identifier, $data = array(), $origin = NULL ): int
	{
		return $this->model->add([
			'userId'		=> $this->userId,
			'status'		=> Model_Event::STATUS_NEW,
			'scope'			=> $this->scope,
			'identifier'	=> $identifier,
			'origin'		=> $origin,
			'data'			=> json_encode( $data ),
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		]);
	}

	/**
	 *	Return number of events matching given conditions.
	 *	If scope is set and not in conditions, it will be added.
	 *	@access		public
	 *	@param		array		$conditions		Map of conditions to match with
	 *	@return		integer
	 */
	public function count( $conditions ): int
	{
		if( !$this->scope && !array_key_exists( 'scope', $conditions ) )
			$conditions['scope']	= $this->scope;
		return $this->model->count( $conditions );
	}

	/**
	 *	Return event by given ID.
	 *	@access		public
	 *	@param		string		$eventId		ID of event to return
	 *	@return		object|NULL	Data object of event
	 */
	public function get( $eventId ): int
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
	public function getByConditions( $conditions ): array
	{
		if( !$this->scope && !array_key_exists( 'scope', $conditions ) )
			$conditions['scope']	= $this->scope;
		return $this->model->getByIndices( $conditions, ['createdAt' => 'ASC'] );
	}

	/**
	 *	Sets event status to "new".
	 *	@access		public
	 *	@param		string		$eventId		ID of event to mark as new
	 *	@param		mixed		$result			Results to store
	 *	@return		self
	 */
	public function markAsNew( $eventId, $result = NULL ): self
	{
		return $this->setStatus( $eventId, Model_Event::STATUS_NEW, $result );
	}

	/**
	 *	Sets event status to "ignored".
	 *	@access		public
	 *	@param		string		$eventId		ID of event to mark as ignored
	 *	@param		mixed		$result			Results to store
	 *	@return		self
	 */
	public function markAsIgnored( $eventId, $result = NULL ): self
	{
		return $this->setStatus( $eventId, Model_Event::STATUS_IGNORED, $result );
	}

	/**
	 *	Sets event status to "revoked".
	 *	@access		public
	 *	@param		string		$eventId		ID of event to mark as revoked
	 *	@param		mixed		$result			Results to store
	 *	@return		self
	 */
	public function markAsRevoked( $eventId, $result = NULL ): self
	{
		return $this->setStatus( $eventId, Model_Event::STATUS_REVOKED, $result );
	}

	/**
	 *	Sets event status to "running".
	 *	@access		public
	 *	@param		string		$eventId		ID of event to mark as running
	 *	@param		mixed		$result			Results to store
	 *	@return		self
	 */
	public function markAsInRunning( $eventId, $result = NULL ): self
	{
		return $this->setStatus( $eventId, Model_Event::STATUS_RUNNING, $result );
	}

	/**
	 *	Sets event status to "failed".
	 *	@access		public
	 *	@param		string		$eventId		ID of event to mark as failed
	 *	@param		mixed		$result			Results to store
	 *	@return		self
	 */
	public function markAsFailed( $eventId, $result = NULL ): self
	{
		return $this->setStatus( $eventId, Model_Event::STATUS_FAILED, $result );
	}

	/**
	 *	Sets event status to "succeeded".
	 *	@access		public
	 *	@param		string		$eventId		ID of event to mark as succeeded
	 *	@param		mixed		$result			Results to store
	 *	@return		self
	 */
	public function markAsSucceeded( $eventId, $result = NULL ): self
	{
		return $this->setStatus( $eventId, Model_Event::STATUS_SUCCEEDED, $result );
	}

	/**
	 *	Sets scope.
	 *	@access		public
	 *	@param		string		$scope			Scope to set
	 *	@return		self
	 */
	public function setScope( $scope ): self
	{
		$this->scope	= $scope;
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->model	= new Model_Event( $this->env );
	}

	protected function setStatus( $eventId, $status, $result = NULL ): self
	{
		$event		= $this->checkEvent( $eventId );
		$possibleStatuses	= Model_Event::STATUSES_TRANSITIONS[$event->status];
		if( !in_array( (int) $status, $possibleStatuses, TRUE ) )
			throw new RangeException( 'Invalid status transition' );
		$data	= [
			'status'		=> $status,
			'result'		=> json_encode( $result ),
			'modifiedAt'	=> time(),
		];
		$this->modelEvent->edit( $eventId, $data );
		return $this;
	}

}
