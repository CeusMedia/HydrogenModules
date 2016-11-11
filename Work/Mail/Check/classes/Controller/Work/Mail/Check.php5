<?php
class Controller_Work_Mail_Check extends CMF_Hydrogen_Controller{

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->modelAddress	= new Model_Mail_Address( $this->env );
		$this->modelCheck	= new Model_Mail_Address_Check( $this->env );
		$this->modelGroup	= new Model_Mail_Group( $this->env );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$addresses	= $this->request->get( 'address' );
			if( !is_array( $addresses ) )
				$addresses	= array( $addresses );
			foreach( $addresses as $address ){
				if( $this->modelAddress->getByIndex( 'address', $address ) ){
					$this->messenger->noteError( 'Already existing: '.htmlentities( $address, ENT_QUOTES, 'UTF-8' ) );
				}
				else{
					$addressId	= $this->modelAddress->add( array(
						'address' => $address,
						'status'	=> 0,
						'createdAt'	=> time(),
					) );
					$this->messenger->noteSuccess( 'Added "'.htmlentities( $address, ENT_QUOTES, 'UTF-8' ).'".' );
				}
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function check(){
		$addressIds	= $this->request->get( 'addressId' );
		if( !is_array( $addressIds ) )
			$addressIds	= array( $addressIds );

		$sender		= new \CeusMedia\Mail\Participant( "dev@ceusmedia.de" );
		$checker	= new \CeusMedia\Mail\Check\Recipient( $sender, TRUE );
		$checker->setVerbose( TRUE );

		foreach( $addressIds as $addressId ){
			$address	= $this->modelAddress->get( $addressId );
			if( !$address ){
				$this->messenger->noteError( 'Invalid address ID.' );
				$this->restart( NULL, TRUE );
			}
			$this->modelAddress->edit( $addressId, array( 'status' => 1 ) );

			$result		= $checker->test( new \CeusMedia\Mail\Participant( $address->address ) );
			$response	= $checker->getLastResponse();
			if( 0 ){
				print_m( $response );
				die;
			}

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
		if( $this->request->get( 'from' ) )
			$this->restart( $this->request->get( 'from' ) );
		$this->restart( NULL, TRUE );
	}

	public function checkAll(){
		$conditions		= array();
		$filterGroupId	= $this->session->set( 'work_mail_check_filter_groupId' );
		if( $filterGroupId )
			$conditions['mailGroupId']	= $filterGroupId;

		$orders			= array( 'address' => 'ASC' );
		$limits			= array( $page * $limit, $limit );
		$addresses		= $this->modelAddress->getAll( $conditions, $orders, $limits );

		foreach( $addresses as $address ){
			$this->modelAddress->edit( $address->mailAddressId, array( 'status' => 1 ) );
		}
		if( $this->request->get( 'from' ) )
			$this->restart( $this->request->get( 'from' ) );
		$this->restart( NULL, TRUE );
	}

	public function filter( $reset = NULL ){
		if( $reset ){
			$this->session->remove( 'work_mail_check_filter_groupId' );
		}
		$this->session->set( 'work_mail_check_filter_groupId', $this->request->get( 'groupId' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 ){
		$limit			= 15;
		$conditions		= array();
		$filterGroupId	= $this->session->get( 'work_mail_check_filter_groupId' );
		if( $filterGroupId )
			$conditions['mailGroupId']	= $filterGroupId;

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