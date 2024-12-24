<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\CSV\Reader as CsvFileReader;
use CeusMedia\Common\FS\File\CSV\Writer as CsvFileWriter;
use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use CeusMedia\Mail\Address as MailAddress;
use CeusMedia\Mail\Address\Check\Availability as MailAvailabilityCheck;
use CeusMedia\Mail\Transport\SMTP\Code as SmtpCode;

class Controller_Work_Mail_Check extends Controller
{
	protected MessengerResource $messenger;
	protected HttpRequest $request;
	protected Dictionary $session;
	protected Dictionary $moduleOptions;
	protected Model_Mail_Address $modelAddress;
	protected Model_Mail_Address_Check $modelCheck;
	protected Model_Mail_Group $modelGroup;

	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			$addresses	= $this->request->get( 'address' );
			$groupId	= $this->request->get( 'groupId' );
			$group		= $this->checkGroupId( $groupId, FALSE, 'group' );
			if( !is_array( $addresses ) )
				$addresses	= [$addresses];
			foreach( $addresses as $address ){
				if( !strlen( trim( $address ) ) )
					continue;
				$indices	= ['mailGroupId' => $groupId, 'address' => $address];
				if( $this->modelAddress->getByIndices( $indices ) ){
					$this->messenger->noteError( 'Address &quot;%s&quot; is already existing in group &quot;%s&quot;.', $address, $group->title );
					$this->restart( NULL, TRUE );
				}
				$addressId	= $this->modelAddress->add( [
					'mailGroupId'	=> $groupId,
					'address'		=> $address,
					'status'		=> 0,
					'createdAt'		=> time(),
				] );
				$this->messenger->noteSuccess( 'Added address "%s".', htmlentities( $address, ENT_QUOTES, 'UTF-8' ) );
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function addGroup(): void
	{
		if( $this->request->has( 'save' ) ){
			$title		= $this->request->get( 'title' );
			$mailColumn	= $this->request->get( 'mailColumn' );
			$columns	= array_filter( explode( "\n", trim( $this->request->get( 'columns' ) ) ) );
			if( !strlen( trim( $title ) ) ){
				$this->messenger->noteError( 'Group title is missing.' );
				$this->restart( 'group', TRUE );
			}
			if( !strlen( trim( $mailColumn ) ) )
				$mailColumn	= "Email";
			foreach( $columns as $nr => $column )
				$columns[$nr]	= trim( $column );
			array_unshift( $columns, $mailColumn );
			$this->modelGroup->add( [
				'title'			=> $title,
				'mailColumn'	=> $mailColumn,
				'columns'		=> json_encode( $columns ),
				'createdAt'		=> time(),
			] );
	//		$this->messenger->noteSuccess( 'Group "%s" added.', htmlentities( $title, ENT_QUOTES, 'UTF-8' ) );
			$this->messenger->noteSuccess( 'Group "%s" added.', $title );
		}
		$this->restart( 'group', TRUE );
	}

	public function check(): void
	{
		$addressIds	= $this->request->get( 'addressId' );
		if( !is_array( $addressIds ) )
			$addressIds	= [$addressIds];

		$sender		= new MailAddress( $this->moduleOptions->get( 'sender' ) );
		$checker	= new MailAvailabilityCheck( $sender );
#		$checker->setVerbose( TRUE );

		foreach( $addressIds as $addressId ){
			$address	= $this->modelAddress->get( $addressId );
			if( !$address ){
				$this->messenger->noteError( 'Invalid address ID.' );
				$this->restart( NULL, TRUE );
			}
			$this->modelAddress->edit( $addressId, ['status' => 1] );

			try{
				$result		= $checker->test( new MailAddress( $address->address ) );
				$response	= $checker->getLastResponse();

				$this->modelCheck->add( [
					'mailAddressId'	=> $addressId,
					'status'		=> $result ? 1 : -1,
					'error'			=> $response->error,
					'code'			=> $response->code,
					'message'		=> $response->message,
					'createdAt'		=> time(),
				] );
				$status	= 2;
				if( !$result ){
					$status	= -2;
					if( substr( $response->code, 0, 1 ) == "4" )
						$status	= -1;
				}
				$this->modelAddress->edit( $addressId, [
					'status'	=> $status,
					'checkedAt'	=> time(),
				] );
		//		$this->messenger->noteSuccess( 'Checked.' );
			}
			catch( Exception $e ){
				$this->modelCheck->add( [
					'mailAddressId'	=> $addressId,
					'status'		=> -2,
					'error'			=> 0,
					'code'			=> $e->getCode(),
					'message'		=> $e->getMessage(),
					'createdAt'		=> time(),
				] );
				$this->modelAddress->edit( $addressId, [
					'status'	=> -2,
					'checkedAt'	=> time(),
				] );
		//		$this->messenger->noteError( 'Check failed: '.$e->getMessage() );
			}
		}
		if( $this->request->get( 'from' ) )
			$this->restart( $this->request->get( 'from' ) );
		$this->restart( NULL, TRUE );
	}

	public function checkAll(): void
	{
		$conditions		= [];
		$filterGroupId	= $this->session->get( 'work_mail_check_filter_groupId' );
		$filterStatus	= $this->session->get( 'work_mail_check_filter_status' );
		$filterQuery	= $this->session->get( 'work_mail_check_filter_query' );
		if( $filterGroupId )
			$conditions['mailGroupId']	= $filterGroupId;
		if( $filterStatus && $filterStatus[0] !== '' )
			$conditions['status']		= $filterStatus;
		if( $filterQuery && strlen( $filterQuery ) )
			$conditions['address']		= '%'.str_replace( '*', '%', $filterQuery ).'%';

		$this->modelAddress->editByIndices( $conditions, ['status' => 1] );
		$this->restart( 'status/'.$filterGroupId, TRUE );
/*		if( $this->request->get( 'from' ) )
			$this->restart( $this->request->get( 'from' ) );
		$this->restart( NULL, TRUE );*/
	}

	public function export( $show = FALSE ): void
	{
		if( $this->request->has( 'save' ) ){

			$words	= $this->getWords();

			$type		= $this->request->get( 'type' );
			$groupId	= $this->request->get( 'groupId' );
			if( !$groupId ){
				$this->messenger->noteError( 'Group ID is missing.' );
				$this->restart( 'export', TRUE );
			}
			$group		= $this->modelGroup->get( $groupId );
			$conditions	= [
				'status'		=> $this->request->get( 'status' ),
				'mailGroupId'	=> $groupId,
			];
			$addresses	= $this->modelAddress->getAll( $conditions, ['address' => 'ASC'], [10, 0] );
			$data		= [];

			$columns	= array_merge( json_decode( $group->columns ), [
				'Code',
				'Code-Beschreibung',
				'Fehler-Code',
				'Fehler-Beschreibung',
				'Server-Meldung',
			] );
			foreach( $addresses as $address ){
				$check	= $this->modelCheck->getByIndices(
					['mailAddressId' => $address->mailAddressId],
					['mailAddressCheckId' => 'DESC']
				);
				if( !strlen( $address->data ) )
					$address->data	= '[]';
				$additionalData	= json_decode( $address->data, TRUE );
				$data[]			= array_merge(
					[trim( $address->address )],
	 				array_values( $additionalData ),
					[
						$check->code,
						SmtpCode::getText( $check->code ),
						$words['errorCodes'][$check->error],
						$words['errorLabels'][$check->error],
						$check->message,
					]
				);
			}
			switch( $type ){
				case 'CSV':
				default:
					$extension	= '.csv';
					$fileName	= tempnam( sys_get_temp_dir(), 'export' );
					$writer		= new CsvFileWriter( $fileName, ';' );
					$writer->write( $data, $columns );
			}
			$date	= date( 'Y-m-d' );
			HttpDownload::sendFile( $fileName, $group->title.'_'.$date.$extension, TRUE );
		}
		$this->addData( 'groups', $this->modelGroup->getAll( [], ['title' => 'ASC'] ) );
	}

	public function filter( $reset = NULL ): void
	{
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

	public function group(): void
	{
		$groups	= $this->modelGroup->getAll( [], ['title' => 'ASC'] );
		foreach( $groups as $group ){
			/** @var array<Entity_Address> $addresses */
			$addresses	= $this->modelAddress->getAll( ['mailGroupId' => $group->mailGroupId] );
			$group->numbers	= (object) [
				'total'		=> count( $addresses ),
				'negative'	=> 0,
				'positive'	=> 0,
				'untested'	=> 0,
				'tested'	=> 0,
			];
			foreach( $addresses as $address ){
				if( in_array( $address->status, [-2, -1] ) )
					$group->numbers->negative++;
				else if( in_array( $address->status, [0, 1] ) )
					$group->numbers->untested++;
				else if( $address->status == 2 )
					$group->numbers->positive++;
			}
			$group->numbers->tested	= $group->numbers->total - $group->numbers->untested;
		}
		$this->addData( 'groups', $groups );
	}

	public function import( $abort = NULL ): void
	{
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
					$reader		= new CsvFileReader( $file->tmp_name, TRUE );
					$this->session->set( 'addressesToImport', (object) [
						'type'		=> 'CSV',
						'mimeType'	=> $file->type,
						'name'		=> basename( $file->name, '.csv' ),
						'size'		=> $file->size,
						'count'		=> $reader->count(),
						'columns'	=> $reader->getHeaders(),
						'data'		=> $reader->toArray(),
					] );
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
			$groupId	= $this->modelGroup->add( [
				'title'			=> $group,
				'createdAt'		=> time(),
				'columns'		=> json_encode( $importData->columns ),
				'mailColumn'	=> $column,
			] );
			foreach( $importData->data as $item ){
				$address	= $item[$column];
				unset( $item[$column] );
				$this->modelAddress->add( [
					'mailGroupId'	=> $groupId,
					'address'		=> $address,
					'data'			=> json_encode( $item ),
					'createdAt'		=> time(),
				] );
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
			$this->addData( 'groups', $this->modelGroup->getAll( [], ['title' => 'ASC'] ) );
		}
	}

	public function index( int $page = 0 ): void
	{
		$limit			= 20;															//  @todo	 replace by configurable default limit (not existing in config atm)
		if( !$this->session->get( 'work_mail_check_filter_limit' ) )
			$this->session->set( 'work_mail_check_filter_limit', $limit );

		$groups	= $this->modelGroup->getAll( [], ['title' => 'ASC'] );
		if( !$this->session->get( 'work_mail_check_filter_groupId' ) && count( $groups ) )
			$this->session->set( 'work_mail_check_filter_groupId', $groups[0]->mailGroupId );

		$filterGroupId	= $this->session->get( 'work_mail_check_filter_groupId' );
		$filterStatus	= $this->session->get( 'work_mail_check_filter_status' );
		$filterQuery	= $this->session->get( 'work_mail_check_filter_query' );
		$filterLimit	= $this->session->get( 'work_mail_check_filter_limit' );

		$conditions		= ['mailGroupId' => $filterGroupId];
		if( $filterStatus && $filterStatus[0] !== '' )
			$conditions['status']		= $filterStatus;
		if( $filterQuery && strlen( $filterQuery ) )
			$conditions['address']		= '%'.str_replace( '*', '%', $filterQuery ).'%';

		$orders			= ['address' => 'ASC'];
		$limits			= [$page * $filterLimit, $filterLimit];
		$total			= $this->modelAddress->count( $conditions );
		$addresses		= $this->modelAddress->getAll( $conditions, $orders, $limits );
		foreach( $addresses as $address ){
			if( !in_array( $address->status, [0, 1] ) ){											//  @todo	why exclude status 1 as well? a retesting address has a history eventually
				$address->check	= $this->modelCheck->getByIndices(
					['mailAddressId' => $address->mailAddressId],
					['mailAddressCheckId' => 'DESC']
				);
			}
		}
		$this->addData( 'addresses', $addresses );

		$indices		= [];
		if( $filterGroupId )
			$indices['mailGroupId']	= $filterGroupId;
		$countByStatus	= [
			-2	=> $this->modelAddress->countByIndices( array_merge( $indices, ['status' => -2] ) ),
			-1	=> $this->modelAddress->countByIndices( array_merge( $indices, ['status' => -1] ) ),
			0	=> $this->modelAddress->countByIndices( array_merge( $indices, ['status' => 0] ) ),
			1	=> $this->modelAddress->countByIndices( array_merge( $indices, ['status' => 1] ) ),
			2	=> $this->modelAddress->countByIndices( array_merge( $indices, ['status' => 2] ) ),
		];

		$countByGroup	= [];
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

	public function remove(): void
	{
		$addressIds	= $this->request->get( 'addressId' );
		if( !is_array( $addressIds ) )
			$addressIds	= [$addressIds];

		foreach( $addressIds as $addressId ){
			$this->modelCheck->removeByIndex( 'mailAddressId', $addressId );
			$this->modelAddress->remove( $addressId );
		}
		$this->restart( NULL, TRUE );
	}

	public function removeGroup( string $groupId ): void
	{
		$group	= $this->checkGroupId( $groupId );
		foreach( $this->modelAddress->getAllByIndex( 'mailGroupId', $groupId ) as $address )
			$this->modelCheck->removeByIndex( 'mailAddressId', $address->mailAddressId );
		$this->modelAddress->removeByIndex( 'mailGroupId', $groupId );
		$this->modelGroup->remove( $groupId );
		$this->restart( 'group', TRUE );
	}

	public function status( string $groupId ): void
	{
		$group		= $this->checkGroupId( $groupId );
		$indices	= ['mailGroupId' => $groupId];
		$this->setData( [
			'total'		=> $this->modelAddress->countByIndices( $indices ),
			'open'		=> $this->modelAddress->countByIndices( array_merge( $indices, [
				'status'	=> 1,
			] ) ),
			'negative'	=> $this->modelAddress->countByIndices( array_merge( $indices, [
				'status'	=> [-2, -1],
			] ) ),
			'positive'	=> $this->modelAddress->countByIndices( array_merge( $indices, [
				'status'	=> 2,
			] ) ),
		] );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
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
	 *	Otherwise, a user message will be noted followed by a redirection.
	 *	The default redirection is to the root of this module.
	 *	Redirections can be set by third "from" parameter.
	 *	Disabling the fourth "withinModule" parameter will unlock the module scope for redirections.
	 *
	 *	@access		protected
	 *	@param		string			$groupId		ID of mail check group to check
	 *	@param		boolean			$strict			Flag: throw exception if not existing
	 *	@param		string|NULL		$from			Path to redirect to
	 *	@param		boolean			$withinModule	Flag: reduce scope of redirection to current module
	 *	@return		object|NULL						Group data entity
	 *	@throws		InvalidArgumentException		if group is not existing and strict mode is enabled
	 *	@todo		Implement OAuth user focus for ASP solution
	 */
	protected function checkGroupId( string $groupId, bool $strict = TRUE, string $from = NULL, bool $withinModule = TRUE ): ?object
	{
		if( $group = $this->modelGroup->get( $groupId ) )
			return $group;
		if( $strict )
			throw new InvalidArgumentException( 'Invalid group ID' );
		$this->messenger->noteError( 'Invalid group ID.' );
		if( $from )
			$this->restart( $from, $withinModule );
		$this->restart( NULL, $withinModule );
		return NULL;
	}
}
