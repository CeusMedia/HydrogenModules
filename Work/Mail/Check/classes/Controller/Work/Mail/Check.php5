<?php
class Controller_Work_Mail_Check extends CMF_Hydrogen_Controller{

	protected $request;
	protected $session;
	protected $messenger;
	protected $modelAddress;
	protected $modelCheck;
	protected $modelGroup;
	protected $options;

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->modelAddress	= new Model_Mail_Address( $this->env );
		$this->modelCheck	= new Model_Mail_Address_Check( $this->env );
		$this->modelGroup	= new Model_Mail_Group( $this->env );
		$this->options		= $this->env->getConfig()->getAll( 'module.work_mail_check.', TRUE );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$addresses	= $this->request->get( 'address' );
			$groupId	= $this->request->get( 'groupId' );
			if( !is_array( $addresses ) )
				$addresses	= array( $addresses );
			foreach( $addresses as $address ){
				if( $this->modelAddress->getByIndex( 'address', $address ) ){
					$this->messenger->noteError( 'Already existing: '.htmlentities( $address, ENT_QUOTES, 'UTF-8' ) );
				}
				else{
					$addressId	= $this->modelAddress->add( array(
						'mailGroupId'	=> $groupId,
						'address'		=> $address,
						'status'		=> 0,
						'createdAt'		=> time(),
					) );
					$this->messenger->noteSuccess( 'Added "'.htmlentities( $address, ENT_QUOTES, 'UTF-8' ).'".' );
				}
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function ajaxEditAddress(){
		$addressId	= $this->request->get( 'id' );
		$address	= $this->request->get( 'address' );

		$result	= FALSE;
		if( $this->modelAddress->get( $addressId ) ){
			$this->modelAddress->edit( $addressId, array(
				'address'	=> $address,
				'status'	=> 0
			) );
			$result	= TRUE;
		}
		print( json_encode( $result ) );
		exit;
	}

	public function check(){
		$addressIds	= $this->request->get( 'addressId' );
		if( !is_array( $addressIds ) )
			$addressIds	= array( $addressIds );

		$sender		= new \CeusMedia\Mail\Participant( $this->options->get( 'sender' ) );
		$checker	= new \CeusMedia\Mail\Check\Recipient( $sender, TRUE );
		$checker->setVerbose( TRUE );

		foreach( $addressIds as $addressId ){
			$address	= $this->modelAddress->get( $addressId );
			if( !$address ){
				$this->messenger->noteError( 'Invalid address ID.' );
				$this->restart( NULL, TRUE );
			}
			$this->modelAddress->edit( $addressId, array( 'status' => 1 ) );

			try{
				$result		= $checker->test( new \CeusMedia\Mail\Participant( $address->address ) );
				$response	= $checker->getLastResponse();

				$this->modelCheck->add( array(
					'mailAddressId'	=> $addressId,
					'status'		=> $result ? 1 : -1,
					'error'			=> $response->error,
					'code'			=> $response->code,
					'message'		=> $response->message,
					'createdAt'		=> time(),
				) );
				$this->modelAddress->edit( $addressId, array(
					'status'	=> $result ? 2 : -1,
					'checkedAt'	=> time(),
				) );
				$this->messenger->noteSuccess( 'Checked.' );
			}
			catch( Exception $e ){
				$this->modelCheck->add( array(
					'mailAddressId'	=> $addressId,
					'status'		=> -2,
					'error'			=> 0,
					'code'			=> $e->getCode(),
					'message'		=> $e->getMessage(),
					'createdAt'		=> time(),
				) );
				$this->modelAddress->edit( $addressId, array(
					'status'	=> -1,
					'checkedAt'	=> time(),
				) );
				$this->messenger->noteError( 'Check failed: '.$e->getMessage() );
			}
		}
		if( $this->request->get( 'from' ) )
			$this->restart( $this->request->get( 'from' ) );
		$this->restart( NULL, TRUE );
	}

	public function checkAll(){
		$conditions		= array();
		$filterGroupId	= $this->session->get( 'work_mail_check_filter_groupId' );
		if( $filterGroupId )
			$conditions['mailGroupId']	= $filterGroupId;

		foreach( $this->modelAddress->getAll( $conditions ) as $address ){
			$this->modelAddress->edit( $address->mailAddressId, array( 'status' => 1 ) );
		}
		$this->restart( 'status/'.$filterGroupId, TRUE );
/*		if( $this->request->get( 'from' ) )
			$this->restart( $this->request->get( 'from' ) );
		$this->restart( NULL, TRUE );*/
	}

	public function filter( $reset = NULL ){
		if( $reset ){
			$this->session->remove( 'work_mail_check_filter_groupId' );
			$this->session->remove( 'work_mail_check_filter_status' );
			$this->session->remove( 'work_mail_check_filter_query' );
		}
		$this->session->set( 'work_mail_check_filter_groupId', $this->request->get( 'groupId' ) );
		$this->session->set( 'work_mail_check_filter_status', $this->request->get( 'status' ) );
		$this->session->set( 'work_mail_check_filter_query', $this->request->get( 'query' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 ){
		$limit			= 20;
		$conditions		= array();
		$filterGroupId	= $this->session->get( 'work_mail_check_filter_groupId' );
		$filterStatus	= $this->session->get( 'work_mail_check_filter_status' );
		$filterQuery	= $this->session->get( 'work_mail_check_filter_query' );
		if( $filterGroupId )
			$conditions['mailGroupId']	= $filterGroupId;
		if( $filterStatus && $filterStatus[0] !== '' )
			$conditions['status']		= $filterStatus;
		if( $filterQuery && strlen( $filterQuery ) )
			$conditions['address']		= '%'.str_replace( '*', '%', $filterQuery ).'%';

		$orders			= array( 'address' => 'ASC' );
		$limits			= array( $page * $limit, $limit );
		$total			= $this->modelAddress->count( $conditions );
		$addresses		= $this->modelAddress->getAll( $conditions, $orders, $limits );
		foreach( $addresses as $address ){
			if( !in_array( $address->status, array( 0, 1 ) ) ){
				$address->check	= $this->modelCheck->getByIndices(
					array( 'mailAddressId' => $address->mailAddressId ),
					"",
					array( 'mailAddressCheckId' => 'DESC' )
				);
			}
		}
		$this->addData( 'addresses', $addresses );
		$this->addData( 'limit', $limit );
		$this->addData( 'page', $page );
		$this->addData( 'total', $total );
		$this->addData( 'groups', $this->modelGroup->getAll() );
		$this->addData( 'filterGroupId', $filterGroupId );
		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterQuery', $filterQuery );
	}

	public function status( $groupId ){
		$group	= $this->modelGroup->get( $groupId );
		if( !$group )
			$this->restart( NULL, TRUE );
		$indices	= array( 'mailGroupId' => $groupId );
		$this->setData( array(
			'total'		=> $this->modelAddress->countByIndices( $indices ),
			'open'		=> $this->modelAddress->countByIndices( array_merge( $indices, array(
				'status'	=> 1,
			) ) ),
			'negative'	=> $this->modelAddress->countByIndices( array_merge( $indices, array(
				'status'	=> -1,
			) ) ),
			'positive'	=> $this->modelAddress->countByIndices( array_merge( $indices, array(
				'status'	=> 2,
			) ) ),
		) );
	}

	public function export(){

		if( $this->request->has( 'save' ) ){
			$type		= $this->request->get( 'type' );
			$groupId	= $this->request->get( 'groupId' );
			$group		= $this->modelGroup->get( $groupId );
			$conditions	= array(
				'status'	=> $this->request->get( 'status' )
			);
			$addresses	= $this->modelAddress->getAll( $conditions, array( 'address' => 'ASC' ), array( 10, 0 ) );
			$data		= array();
			foreach( $addresses as $address ){
				$additionalData	= json_decode( $address->data, TRUE );
				$data[]			= array_merge(
					array( $group->mailColumn => $address->address ),
	 				$additionalData
				);
			}
			switch( $type ){
				case 'CSV':
				default:
					$extension	= '.csv';
					$fileName	= tempnam( sys_get_temp_dir(), 'export' );
					$writer		= new FS_File_CSV_Writer( $fileName, ';' );
					$writer->write( $data, $group->columns );
			}
			$date	= date( 'Y-m-d' );
			Net_HTTP_Download::sendFile( $fileName, $group->title.'_'.$date.$ext, TRUE );
		}
		$this->addData( 'groups', $this->modelGroup->getAll() );
	}

	public function addGroup(){
/*
		$this->modelGroup->add( array(
			'title'		=> $this->request->get( 'title' ),
			'columns'	=> $this->request->get( 'title' )

		) );
*/
	}

	public function removeGroup( $groupId ){
		foreach( $this->modelAddress->getAllByIndex( 'mailGroupId', $groupId ) as $address )
			$this->modelCheck->removeByIndex( 'mailAddressId', $address->mailAddressId );
		$this->modelAddress->removeByIndex( 'mailGroupId', $groupId );
		$this->modelGroup->remove( $groupId );
		$this->restart( 'group', TRUE );
	}

	public function group(){
		$groups	= $this->modelGroup->getAll();
		foreach( $groups as $group ){
			$addresses	= $this->modelAddress->getAll( array( 'mailGroupId' => $group->mailGroupId ) );
			$group->numbers	= (object) array(
				'total'		=> count( $addresses ),
				'negative'	=> 0,
				'positive'	=> 0,
				'untested'	=> 0,
				'tested'	=> 0,
			);
			foreach( $addresses as $address ){
				if( $address->status == -1 )
					$group->numbers->negative++;
				else if( in_array( $address->status, array( 0, 1 ) ) )
					$group->numbers->untested++;
				else if( $address->status == 2 )
					$group->numbers->positive++;
			}
			$group->numbers->tested	= $group->numbers->total - $group->numbers->untested;
		}
		$this->addData( 'groups', $groups );
	}

	public function import( $abort = NULL ){
		if( $abort ){
			$this->session->remove( 'addressesToImport' );
			$this->restart( 'import', TRUE );
		}
		else if( $this->request->get( 'file' ) ){
			$file	= (object) $this->request->get( 'file' );
			$logic	= new Logic_Upload( $this->env );
			$logic->setUpload( $file );
			switch( $logic->getMimeType() ){
				case 'text/csv':
					$reader		= new FS_File_CSV_Reader( $file->tmp_name, TRUE );
					$this->session->set( 'addressesToImport', (object) array(
						'type'		=> 'CSV',
						'mimeType'	=> $file->type,
						'name'		=> basename( $file->name, '.csv' ),
						'size'		=> $file->size,
						'count'		=> $reader->getRowCount(),
						'columns'	=> $reader->getColumnHeaders(),
						'data'		=> $reader->toAssocArray(),
					) );
					break;
			}
			$this->restart( 'import', TRUE );
		}
		else if( $this->request->has( 'save' ) ){
			$importData		= $this->session->get( 'addressesToImport' );
			$column			= $this->request->get( 'column' );
			$group			= $this->request->get( 'group' );
			if( !strlen( trim( $group ) ) ){
				$this->messenger->noteError( 'No group name given.' );
				$this->restart( 'import', TRUE );
			}
			$groupId	= $this->modelGroup->add( array(
				'title'			=> $group,
				'createdAt'		=> time(),
				'columns'		=> json_encode( $importData->columns ),
				'mailColumn'	=> $column,
			) );
			foreach( $importData->data as $item ){
				$address	= $item[$column];
				unset( $item[$column] );
				$this->modelAddress->add( array(
					'mailGroupId'	=> $groupId,
					'address'		=> $address,
					'data'			=> json_encode( $item ),
					'createdAt'		=> time(),
				) );
			}
			$this->session->remove( 'addressesToImport' );
			$this->restart( 'filter/?groupId='.$groupId, TRUE );
		}
		else if( $this->session->has( 'addressesToImport' ) ){
			$data	= $this->session->get( 'addressesToImport' );
			$this->addData( 'type', $data->type );
			$this->addData( 'mimeType', $data->mimeType );
			$this->addData( 'name', $data->name );
			$this->addData( 'size', $data->size );
			$this->addData( 'count', $data->count );
			$this->addData( 'columns', $data->columns );
			$this->addData( 'groups', $this->modelGroup->getAll() );
		}
	}


	public function remove(){
		$addressIds	= $this->request->get( 'addressId' );
		if( !is_array( $addressIds ) )
			$addressIds	= array( $addressIds );

		foreach( $addressIds as $addressId ){
			$this->modelCheck->removeByIndex( 'mailAddressId', $addressId );
			$this->modelAddress->remove( $addressId );
		}
		$this->restart( NULL, TRUE );
	}

}
?>
