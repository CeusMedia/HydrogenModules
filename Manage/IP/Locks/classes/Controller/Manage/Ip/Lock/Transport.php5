<?php
class Controller_Manage_Ip_Lock_Transport extends CMF_Hydrogen_Controller{

	protected function __onInit(){
		$this->modelFilter		= new Model_IP_Lock_Filter( $this->env );
		$this->modelReason		= new Model_IP_Lock_Reason( $this->env );
	}

	public function index(){
		$this->addData( 'filters', $this->modelFilter->getAll() );
		$this->addData( 'reasons', $this->modelReason->getAll() );
	}

	public function export(){
		$data	= array(
			'reasons' => $this->modelReason->getAll(),
			'filters' => $this->modelFilter->getAll(),
		);
		$json	= json_encode( $data, JSON_PRETTY_PRINT );
		$fileName	= 'IP_lock_'.date( 'y-m-d' ).'.json';
		Net_HTTP_Download::sendString( $json, $fileName, TRUE );
//		$this->restart( NULL, TRUE );
	}

	public function import(){
		$request	= $this->env->getRequest();
		print_m( $this->env->getRequest()->getAll() );

		$upload	= new Logic_Upload( $this->env );
		try{
			$upload->setUpload( $request->get( 'upload' ) );
			if( !$upload->checkSize( '1MB' ) )
				throw new RuntimeException( 'Die Datei ist zu groß (max. 1MB)' );
			if( !$upload->checkExtension( array( 'json' ) ) )
				throw new RuntimeException( 'Die Datei muss eine JSON-Datei sein (endet auf <tt>.json</tt>)' );
			if( !$upload->checkMimeType( array( 'application/json' ) ) )
				throw new RuntimeException( 'Datei muss Daten im JSON-Format beinhalten.' );
			if( !$upload->getError() ){
				$data	= json_decode( $upload->getContent(), FALSE );
				$type	= $request->get( 'type' );
				switch( $type ){
					case 'fresh':
						$this->importFresh( $data );
						break;
					case 'merge':
						$this->importWithMerge( $data );
						break;
					default:
						throw new RangeException( 'Invalid import type: '.$type );
				}
			}
		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteError( $e->getMessage().'.' );
		}
		$this->restart( NULL, TRUE );
	}

	protected function importFresh( $data ){
		$this->modelReason->truncate();
		$this->modelFilter->truncate();
		$modelLock	= new Model_IP_Lock( $this->env );
		$modelLock->truncate();
		foreach( $data->reasons as $reason )
			$this->modelReason->add( (array) $reason, FALSE );
		foreach( $data->filters as $filter )
			$this->modelFilter->add( (array) $filter, FALSE );
	}

	protected function importWithMerge( $data ){
		$reasons	= array();
		foreach( $data->reasons as $reason ){
			$importId			= $reason->ipLockReasonId;
			$reason->appliedAt	= 0;
			unset( $reason->ipLockReasonId );
			$reasons[$importId]	= $reason;
		}
		$filters	= array();
		foreach( $data->filters as $filter ){
			$importId			= $filter->ipLockFilterId;
			$filter->appliedAt	= 0;
			unset( $filter->ipLockFilterId );
			$filters[$importId]	= $filter;
		}
		foreach( $reasons as $reasonImportId => $reason ){
			$reasonId	= $this->modelReason->add( (array) $reason, FALSE );
			$reason->ipLockReasonId	= $reasonId;
		}
		foreach( $filters as $filterImportId => $filter ){
			$filter->reasonId	= $reasons[$filter->reasonId]->ipLockReasonId;
			$this->modelFilter->add( (array) $filter, FALSE );
		}
	}
}
