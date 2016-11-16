<?php
class Controller_Work_Mail_Check extends CMF_Hydrogen_Controller{

	protected $messenger;
	protected $modelAddress;
	protected $modelCheck;
	protected $modelGroup;
	protected $options;
	protected $request;
	protected $session;

	protected function __onInit(){
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->moduleOptions	= $this->env->getConfig()->getAll( 'module.work_mail_check.', TRUE );

		//  --  PREPARE MODELS  --  //
		$this->modelAddress		= new Model_Mail_Address( $this->env );
		$this->modelCheck		= new Model_Mail_Address_Check( $this->env );
		$this->modelGroup		= new Model_Mail_Group( $this->env );
	}


	/**
	 *	Checks whether a group ID is existing and returns data entity.
	 *	Otherwise, in default strict mode, an exception will be thrown.
	 *	Otherwise a user message will be noted followed by a redirection.
	 *	The default redirection is to the root of this module.
	 *	Redirections can be set by third "from" parameter.
	 *	Disabling the fourth "withinModule" parameter will unlock the module scope for redirections.
	 *
	 *	@access		protected
	 *	@param		integer		$groupId		ID of mail check group to check
	 *	@param		boolean		$strict			Flag: throw exception it not existing
	 *	@param		string		$from			Path to redirect to
	 *	@param		boolean		$withinModule	Flag: reduce scope of redirection to current module
	 *	@return		object|array				Group data entity
	 *	@throws		InvalidArgumentException	if group is not existing and strict mode is enabled
	 *	@todo		kriss: implement OAuth user focus for ASP solution
	 */
	protected function checkGroupId( $groupId, $strict = TRUE, $from = NULL, $withinModule = TRUE ){
		if( $group = $this->modelGroup->get( $groupId ) )
			return $group;
		if( $strict )
			throw new InvalidArgumentException( 'Invalid group ID' );
		$this->messenger->noteError( 'Invalid group ID.' );
		if( $from )
			$this->restart( $from, $withinModule );
		$this->restart( NULL, $withinModule );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$addresses	= $this->request->get( 'address' );
			$groupId	= $this->request->get( 'groupId' );
			$group		= $this->checkGroupId( $groupId, FALSE, 'group' );
			if( !is_array( $addresses ) )
				$addresses	= array( $addresses );
			foreach( $addresses as $address ){
				if( !strlen( trim( $address ) ) )
					continue;
				$indices	= array( 'mailGroupId' => $groupId, 'address' => $address );
				if( $this->modelAddress->getByIndices( $indices ) ){
					$this->messenger->noteError( 'Address &quot;%s&quot; is already existing in group &quot;%s&quot;.', $address, $group->title );
					$this->restart( NULL, TRUE );
				}
				$addressId	= $this->modelAddress->add( array(
					'mailGroupId'	=> $groupId,
					'address'		=> $address,
					'status'		=> 0,
					'createdAt'		=> time(),
				) );
				$this->messenger->noteSuccess( 'Added address "%s".', htmlentities( $address, ENT_QUOTES, 'UTF-8' ) );
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function addGroup(){
		if( $this->request->has( 'save' ) ){
			$title		= $this->request->get( 'title' );
			$mailColumn	= $this->request->get( 'mailColumn' );
			$columns	= trim( $this->request->get( 'columns' ) );
			if( !strlen( trim( $title ) ) ){
				$this->messenger->noteError( 'Group title is missing.' );
				$this->restart( 'group', TRUE );
			}
			if( !strlen( trim( $mailColumn ) ) )
				$mailColumn	= "Email";
			foreach( explode( "\n", $columns ) as $nr => $column )
				$columns[$nr]	= trim( $column );
			array_unshift( $columns, $mailColumn );
			$this->modelGroup->add( array(
				'title'			=> $title,
				'mailColumn'	=> $mailColumn,
				'columns'		=> json_encode( $columns ),
				'createdAt'		=> time(),
			) );
	//		$this->messenger->noteSuccess( 'Group "%s" added.', htmlentities( $title, ENT_QUOTES, 'UTF-8' ) );
			$this->messenger->noteSuccess( 'Group "%s" added.', $title );
		}
		$this->restart( 'group', TRUE );
	}

	public function ajaxAddress( $addressId ){
		$address	= $this->modelAddress->get( $addressId );
		if( $address ){
			$address->checks	= $this->modelCheck->getAllByIndex( 'mailAddressId', $addressId, array( 'createdAt' => 'DESC' ) );
			$this->addData( 'addressId', $addressId );
			$this->addData( 'address', $address );
		}
	}

	public function ajaxEditAddress(){
		$addressId	= $this->request->get( 'id' );
		$address	= $this->request->get( 'address' );

		$result	= FALSE;
		if( $this->modelAddress->get( $addressId ) ){
			$this->modelAddress->edit( $addressId, array(
				'address'	=> trim( $address ),
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

		$sender		= new \CeusMedia\Mail\Participant( $this->moduleOptions->get( 'sender' ) );
		$checker	= new \CeusMedia\Mail\Check\Recipient( $sender, TRUE );
		$checker->setVerbose( !TRUE );

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
				$status	= 2;
				if( !$result ){
					$status	= -2;
					if( substr( $response->code, 0, 1 ) == "4" )
						$status	= -1;
				}
				$this->modelAddress->edit( $addressId, array(
					'status'	=> $status,
					'checkedAt'	=> time(),
				) );
		//		$this->messenger->noteSuccess( 'Checked.' );
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
					'status'	=> -2,
					'checkedAt'	=> time(),
				) );
		//		$this->messenger->noteError( 'Check failed: '.$e->getMessage() );
			}
		}
		if( $this->request->get( 'from' ) )
			$this->restart( $this->request->get( 'from' ) );
		$this->restart( NULL, TRUE );
	}

	public function checkAll(){
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

		$this->modelAddress->editByIndices( $conditions, array( 'status' => 1 ) );
		$this->restart( 'status/'.$filterGroupId, TRUE );
/*		if( $this->request->get( 'from' ) )
			$this->restart( $this->request->get( 'from' ) );
		$this->restart( NULL, TRUE );*/
	}

	public function export( $show = FALSE ){
		if( $this->request->has( 'save' ) ){

			$words	= $this->getWords();

			$type		= $this->request->get( 'type' );
			$groupId	= $this->request->get( 'groupId' );
			if( !$groupId ){
				$this->messenger->noteError( 'Group ID is missing.' );
				$this->restart( 'export', TRUE );
			}
			$group		= $this->modelGroup->get( $groupId );
			$conditions	= array(
				'status'		=> $this->request->get( 'status' ),
				'mailGroupId'	=> $groupId,
			);
			$addresses	= $this->modelAddress->getAll( $conditions, array( 'address' => 'ASC' ), array( 10, 0 ) );
			$data		= array();

			$columns	= array_merge( json_decode( $group->columns ), array(
				'Code',
				'Code-Beschreibung',
				'Fehler',
				'Fehler-Beschreibung',
				'Server-Meldung',
			) );
			foreach( $addresses as $address ){
				$check	= $this->modelCheck->getByIndices(
					array( 'mailAddressId' => $address->mailAddressId ),
					'',
					array( 'mailAddressCheckId' => 'DESC' )
				);
				if( !strlen( $address->data ) )
					$address->data	= '[]';
				$additionalData	= json_decode( $address->data, TRUE );
				$data[]			= array_merge(
					array( trim( $address->address ) ),
	 				array_values( $additionalData ),
					array(
						$check->code,
						\CeusMedia\Mail\Transport\SMTP\Code::getText( $check->code ),
						$check->error,
						$words['errorCodes'][$check->error],
						$words['errorLabels'][$check->error],
						$check->message,
					)
				);
			}
			switch( $type ){
				case 'CSV':
				default:
					$extension	= '.csv';
					$fileName	= tempnam( sys_get_temp_dir(), 'export' );
					$writer		= new FS_File_CSV_Writer( $fileName, ';' );
					$writer->write( $data, $columns, TRUE );
			}
			$date	= date( 'Y-m-d' );
			Net_HTTP_Download::sendFile( $fileName, $group->title.'_'.$date.$extension, TRUE );
		}
		$this->addData( 'groups', $this->modelGroup->getAll( array(), array( 'title' => 'ASC' ) ) );
	}

	public function filter( $reset = NULL ){
		if( $reset ){
			$this->session->remove( 'work_mail_check_filter_groupId' );
			$this->session->remove( 'work_mail_check_filter_status' );
			$this->session->remove( 'work_mail_check_filter_query' );
			$this->session->remove( 'work_mail_check_filter_limit' );
		}
		$this->session->set( 'work_mail_check_filter_groupId', $this->request->get( 'groupId' ) );
		$this->session->set( 'work_mail_check_filter_status', $this->request->get( 'status' ) );
		$this->session->set( 'work_mail_check_filter_query', trim( $this->request->get( 'query' ) ) );
		$this->session->set( 'work_mail_check_filter_limit', (int) $this->request->get( 'limit' ) );
		$this->restart( NULL, TRUE );
	}

	public function group(){
		$groups	= $this->modelGroup->getAll( array(), array( 'title' => 'ASC' ) );
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
				if( in_array( $address->status, array( -2, -1 ) ) )
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
			$this->addData( 'groups', $this->modelGroup->getAll( array(), array( 'title' => 'ASC' ) ) );
		}
	}

	public function index( $page = 0 ){
		$limit			= 20;															//  @todo	kriss: replace by configurable default limit (not existing in config atm)
		if( !$this->session->get( 'work_mail_check_filter_limit' ) )
			$this->session->set( 'work_mail_check_filter_limit', $limit );

		$groups	= $this->modelGroup->getAll( array(), array( 'title' => 'ASC' ) );
		if( !$this->session->get( 'work_mail_check_filter_groupId' ) && count( $groups ) )
			$this->session->set( 'work_mail_check_filter_groupId', $groups[0]->mailGroupId );

		$filterGroupId	= $this->session->get( 'work_mail_check_filter_groupId' );
		$filterStatus	= $this->session->get( 'work_mail_check_filter_status' );
		$filterQuery	= $this->session->get( 'work_mail_check_filter_query' );
		$filterLimit	= $this->session->get( 'work_mail_check_filter_limit' );

		$conditions		= array( 'mailGroupId' => $filterGroupId );
		if( $filterStatus && $filterStatus[0] !== '' )
			$conditions['status']		= $filterStatus;
		if( $filterQuery && strlen( $filterQuery ) )
			$conditions['address']		= '%'.str_replace( '*', '%', $filterQuery ).'%';

		$orders			= array( 'address' => 'ASC' );
		$limits			= array( $page * $filterLimit, $filterLimit );
		$total			= $this->modelAddress->count( $conditions );
		$addresses		= $this->modelAddress->getAll( $conditions, $orders, $limits );
		foreach( $addresses as $address ){
			if( !in_array( $address->status, array( 0, 1 ) ) ){											//  @todo	kriss: why exclude status 1 aswell? a retesting address has a history eventually
				$address->check	= $this->modelCheck->getByIndices(
					array( 'mailAddressId' => $address->mailAddressId ),
					"",
					array( 'mailAddressCheckId' => 'DESC' )
				);
			}
		}
		$this->addData( 'addresses', $addresses );

		$indices		= array();
		if( $filterGroupId )
			$indices['mailGroupId']	= $filterGroupId;
		$countByStatus	= array(
			-2	=> $this->modelAddress->countByIndices( array_merge( $indices, array( 'status' => -2 ) ) ),
			-1	=> $this->modelAddress->countByIndices( array_merge( $indices, array( 'status' => -1 ) ) ),
			0	=> $this->modelAddress->countByIndices( array_merge( $indices, array( 'status' => 0 ) ) ),
			1	=> $this->modelAddress->countByIndices( array_merge( $indices, array( 'status' => 1 ) ) ),
			2	=> $this->modelAddress->countByIndices( array_merge( $indices, array( 'status' => 2 ) ) ),
		);

		$countByGroup	= array();
		foreach( $groups as $group )
			$countByGroup[$group->mailGroupId]	= $this->modelAddress->countByIndex( 'mailGroupId', $group->mailGroupId );

		$this->addData( 'limit', $filterLimit );
		$this->addData( 'page', $page );
		$this->addData( 'total', $total );
		$this->addData( 'filterGroupId', $filterGroupId );
		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterQuery', $filterQuery );
		$this->addData( 'filterLimit', $filterLimit );
		$this->addData( 'countByStatus', $countByStatus );
		$this->addData( 'countByGroup', $countByGroup );
		$this->addData( 'groups', $groups );
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

	public function removeGroup( $groupId ){
		$group	= $this->checkGroupId( $groupId );
		foreach( $this->modelAddress->getAllByIndex( 'mailGroupId', $groupId ) as $address )
			$this->modelCheck->removeByIndex( 'mailAddressId', $address->mailAddressId );
		$this->modelAddress->removeByIndex( 'mailGroupId', $groupId );
		$this->modelGroup->remove( $groupId );
		$this->restart( 'group', TRUE );
	}

	public function status( $groupId ){
		$group		= $this->checkGroupId( $groupId );
		$indices	= array( 'mailGroupId' => $groupId );
		$this->setData( array(
			'total'		=> $this->modelAddress->countByIndices( $indices ),
			'open'		=> $this->modelAddress->countByIndices( array_merge( $indices, array(
				'status'	=> 1,
			) ) ),
			'negative'	=> $this->modelAddress->countByIndices( array_merge( $indices, array(
				'status'	=> array( -2, -1 ),
			) ) ),
			'positive'	=> $this->modelAddress->countByIndices( array_merge( $indices, array(
				'status'	=> 2,
			) ) ),
		) );
	}
}
?>
