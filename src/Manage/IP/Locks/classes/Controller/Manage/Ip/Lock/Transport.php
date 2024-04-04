<?php

use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Ip_Lock_Transport extends Controller
{
	protected $request;
	protected Logic_IP_Lock_Transport $logicTransport;
	protected Model_IP_Lock_Filter $modelFilter;
	protected Model_IP_Lock_Reason $modelReason;


	public function index(): void
	{
		$reasons	= $this->modelReason->getAll( [], ['title' => 'ASC'] );
		$filters	= $this->modelFilter->getAll( [], ['title' => 'ASC'] );

		foreach( $reasons as $reason )
			$reason->filters	= $this->modelFilter->getAllByIndex(
				'reasonId',
				$reason->ipLockReasonId,
				array( 'title' => 'ASC'
			) );
		$this->addData( 'reasons', $reasons );
		$this->addData( 'filters', $filters );
	}

	public function export(): void
	{
		if( !$this->request->getMethod()->isPost() )
			$this->restart( NULL, TRUE );

		$fileName	= $this->request->get( 'filename', '' );
		$reasonIds	= $this->request->get( 'reasonIds' );
		$filterIds	= $this->request->get( 'filterIds' );

		if( $this->request->get( 'reasons' ) === 'all' )
			$reasonIds	= [];
		if( $this->request->get( 'filters' ) === 'all' )
			$filterIds	= [];
		$json		= $this->logicTransport->export( $reasonIds, $filterIds );

		if( !strlen( trim( $fileName ) ) )
			$fileName	= 'IP_lock_{DATE}';
		$fileName	= str_replace( '{DATE}', date( 'Y-M-D' ), $fileName );
		if( !preg_match( '/\.\S+$/', $fileName ) )
			$fileName	.= '.json';

		HttpDownload::sendString( $json, $fileName );
	}

	public function import(): void
	{
		$request	= $this->env->getRequest();
//		print_m( $this->env->getRequest()->getAll() );

		$upload	= new Logic_Upload( $this->env );
		try{
			$upload->setUpload( $request->get( 'upload' ) );
			if( !$upload->checkSize( '1MB' ) )
				throw new RuntimeException( 'Die Datei ist zu groß (max. 1MB)' );
			if( !$upload->checkExtension( ['json'] ) )
				throw new RuntimeException( 'Die Datei muss eine JSON-Datei sein (endet auf <tt>.json</tt>)' );
			if( !$upload->checkMimeType( ['application/json'] ) )
				throw new RuntimeException( 'Datei muss Daten im JSON-Format beinhalten.' );
			if( !$upload->getError() ){
				$data	= json_decode( $upload->getContent(), FALSE );
				$reset	= 'fresh' === $request->get( 'type', 'merge' );
				$this->logicTransport->import( $data, $reset );
			}
		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteError( $e->getMessage().'.' );
		}
		$this->restart( NULL, TRUE );
	}

	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->modelFilter		= new Model_IP_Lock_Filter( $this->env );
		$this->modelReason		= new Model_IP_Lock_Reason( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicTransport	= Logic_IP_Lock_Transport::getInstance( $this->env );

//		$logicPool				= $this->env->getLogic();
//		$logicPoolKey			= $logicPool->getKeyFromClassName( 'Logic_IP_Lock_Transport' );
//		$this->logicTransport	= $logicPool->get( $logicPoolKey );
	}
}
