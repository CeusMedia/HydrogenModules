<?php
class Logic_IP_Lock_Transport extends CMF_Hydrogen_Logic{

	protected $modelFilter;
	protected $modelReason;
	protected $modelLock;

	protected $logicLock;
	protected $logicTransport;

	protected function __onInit(){
		$this->modelFilter	= new Model_IP_Lock_Filter( $this->env );
		$this->modelReason	= new Model_IP_Lock_Reason( $this->env );
		$this->modelLock	= new Model_IP_Lock( $this->env );
		$this->logicLock		= $this->env->getLogic()->get( 'ipLock' );
	}

	/**
	 *	Export reasons and filters.
	 *	@access		public
	 *	@param		array		$reasonIds		List if reason IDs (empty: all)
	 *	@param		array		$filterIds		List if filter IDs (empty: all)
	 *	@return		object		Map of reasons and filters (keys: reasons, filters)
	 */
	public function export( $reasonIds = array(), $filterIds = array() ){
		if( !$reasonIds ){
			$reasons	= $this->modelReason->getAll();
			$filters	= $this->modelFilter->getAll();
		}
		else{
			$reasons	= $this->modelReason->getAll( array( 'ipLockReasonId' => $reasonIds ) );
			if( !$filterIds ){
				$filters	= $this->modelFilter->getAllByIndex( 'reasonId', $reasonIds );
			}
			else{
				$reasonFiltersIds	= $this->modelFilter->getAllByIndices(
					array( 'reasonId' => $reasonIds ),
					array(),
					array(),
					array( 'ipLockFilterId' )
				);
				$filterIds	= array_intersect( $reasonFiltersIds, $filterIds );
				$filters	= $this->modelFilter->getAllByIndices( array(
					'reasonId'			=> $reasonIds,
					'ipLockFilterId'	=> $filterIds,
				) );
			}
		}
		return (object) array(
			'reasons' => $reasons,
			'filters' => $filters,
		);
	}

	/**
	 *	Export reasons and filters as JSON string.
	 *	@access		public
	 *	@param		array		$reasonIds		List if reason IDs (empty: all)
	 *	@param		array		$filterIds		List if filter IDs (empty: all)
	 *	@param		boolean		$pretty			Flag: return pretty JSON string
	 *	@return		string		JSON string containing exported reasons and filters
	 */
	public function exportAsJson( $reasonIds = array(), $filterIds = array(), $pretty = FALSE ){
		$data	= $this->export( $reasonIds, $filterIds );
		return json_encode( $data, $pretty ? JSON_PRETTY_PRINT : NULL );
	}

	/**
	 *	Tries to import lists of reasons and filters.
	 *	Will import reasons and filters directly if having none set or reset is forced.
	 *	Other will try to merge new reasons and filters with existing ones.
	 *
	 *	@access		public
	 *	@param		object		Data object containing reasons and filters
	 *	@param		boolean		Flag: clear locks, filters and reasons beforehand
	 *	@return		object		Data object containging number of affected reasons and filters
	 *	@throws		RuntimeException	if import transaction failed
	 */
	public function import( $data, $resetAllBefore = FALSE ){
		$dbc	= $this->env->getDatabase();
		try{
			$dbc->beginTransaction();
			if( $resetAllBefore )
				$this->logicLock->removeAll( TRUE, TRUE, TRUE );
			$hasReasons		= $this->modelReason->count();
			$hasFilters		= $this->modelFilter->count();
			if( !$hasReasons && !$hasFilters ){
				foreach( $data->reasons as $reason )
					$this->modelReason->add( (array) $reason, FALSE );
				foreach( $data->filters as $filter )
					$this->modelFilter->add( (array) $filter, FALSE );
				$result		= (object) array(
					'reasons'	=> count( $data->reasons ),
					'filters'	=> count( $data->filters ),
				);
			}
			else
				$result	= $this->importByMerge( $data );
			$dbc->commit();
		}
		catch( Exception $e ){
			$dbc->rollBack();
			throw new RuntimeException( 'Import failed', 0, $e );
		}
		return $result;
	}

	/**
	 *	Tries to import lists of reasons and filters from JSON string.
	 *	Will import reasons and filters directly if having none set or reset is forced.
	 *	Other will try to merge new reasons and filters with existing ones.
	 *
	 *	@access		public
	 *	@param		object		Data object containing reasons and filters
	 *	@param		boolean		Flag: clear locks, filters and reasons beforehand
	 *	@return		object		Data object containging number of affected reasons and filters
	 *	@throws		RuntimeException	if import transaction failed
	 */
	public function importFromJson( $json, $resetAllBefore = FALSE ){
		$data	= FS_File_JSON_Reader::load( $json );
		$this->import( $data, $resetAllBefore );
	}

	/*  --  PROTECTED  --  */
	protected function importByMerge( $data ){
		$countReasons	= 0;
		$countFilters	= 0;
		$reasonIdMap	= array();
		foreach( $data->reasons as $reason ){
			$importId			= $reason->ipLockReasonId;
			$reason->appliedAt	= 0;
			unset( $reason->ipLockReasonId );
			$reasonIdMap[$importId]	= $reason;
		}
		$filterIdMap	= array();
		foreach( $data->filters as $filter ){
			$importId			= $filter->ipLockFilterId;
			$filter->appliedAt	= 0;
			unset( $filter->ipLockFilterId );
			$filterIdMap[$importId]	= $filter;
		}
		foreach( $reasonIdMap as $reasonImportId => $reason ){
			$existingReason	= $this->modelReason->getByIndices( array( 'title' => $reason->title ) );
			if( $existingReason )
				$reasonIdMap[$reasonImportId]	= $existingReason;
			else{
				$reasonId	= $this->modelReason->add( (array) $reason, FALSE );
				$reason->ipLockReasonId	= $reasonId;
				$reasonIdMap[$reasonImportId]	= $reason;
				$countReasons++;
			}
		}
		foreach( $filterIdMap as $filterImportId => $filter ){
			$existingFilter	= $this->modelFilter->getByIndices( array(
				'method'	=> $filter->method,
				'pattern'	=> $filter->pattern,
			) );
			if( !$existingFilter ){
				$filter->reasonId	= $reasonIdMap[$filter->reasonId]->ipLockReasonId;
				$this->modelFilter->add( (array) $filter, FALSE );
				$countFilters++;
			}
		}
		return (object) array(
			'reasons'	=> $countReasons,
			'filters'	=> $countFilters,
		);
	}
}