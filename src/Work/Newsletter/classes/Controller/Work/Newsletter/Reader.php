<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\CSV\Reader as CsvFileReader;
use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use CeusMedia\Mail\Address\Collection\Parser as MailAddressCollectionParser;

class Controller_Work_Newsletter_Reader extends Controller
{
	/**	@var	Logic_Newsletter_Editor		$logic		Instance of newsletter editor logic */
	protected Logic_Newsletter_Editor $logic;
	protected Dictionary $session;
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Dictionary $moduleConfig;
	protected ?Logic_Limiter $limiter		= NULL;

	protected array $filters		= [
		'email',
		'firstname',
		'surname',
		'status',
		'groupId',
		'limit',
	];
	protected string $filterPrefix	= 'filter_work_newsletter_reader_';

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		$words		= (object) $this->getWords( 'add' );
		if( $this->request->has( 'save' ) ){
			$data		= $this->request->getAll();
			$groupIds	= $this->request->get( 'groupIds' );
			$groupIds	= is_array( $groupIds ) ? $groupIds : [];
			if( !strlen( trim( $data['email'] ) ) )
				$this->messenger->noteError( $words->msgErrorMailMissing );
			else if( !strlen( trim( $data['firstname'] ) ) )
				$this->messenger->noteError( $words->msgErrorFirstnameMissing );
			else if( !strlen( trim( $data['surname'] ) ) )
				$this->messenger->noteError( $words->msgErrorSurnameMissing );
			else{
				$readerId	= $this->logic->addReader( $this->request->getAll() );
				$reader		= $this->logic->getReader( $readerId );
				$this->messenger->noteSuccess( $words->msgSuccess );
				$groups		= [];
				if( $groupIds ){
					foreach( $groupIds as $groupId ){
						$this->logic->addReaderToGroup( $readerId, $groupId );
						$groups[]	= $this->logic->getGroup( $groupId );
					}
				}
				$data	= [
					'readerId'	=> $readerId,
					'reader'	=> $reader,
					'groups'	=> $groups,
				];
				$status	= (int) $this->request->get( 'status' );
				if( $this->request->has( 'inform' ) || $status === Model_Newsletter_Reader::STATUS_REGISTERED ){
					$mail	= new Mail_Work_Newsletter_Invite( $this->env, $data );
					if( $status === Model_Newsletter_Reader::STATUS_CONFIRMED )
						$mail	= new Mail_Work_Newsletter_Add( $this->env, $data );
					$receiver	= (object) [
						'username'	=> $this->request->get( 'firstname' ).' '.$this->request->get( 'surname' ),
						'email'		=> $this->request->get( 'email' ),
					];
					$language		= $this->env->getLanguage()->getLanguage();
					$logicMail		= Logic_Mail::getInstance( $this->env );
					$logicMail->appendRegisteredAttachments( $mail, $language );
					$logicMail->handleMail( $mail, $receiver, $language );
				}

				$this->restart( match( strtolower( $this->request->get( 'nextAction' ) ) ){
					'add'		=> 'add',
					'edit'		=> 'edit/'.$readerId,
					default		=> NULL,
				}, TRUE );
			}
		}
		$reader		= (object) [
			'status'		=> $this->request->get( 'status' ),
			'gender'		=> $this->request->get( 'gender' ),
			'prefix'		=> $this->request->get( 'prefix' ),
			'firstname'		=> $this->request->get( 'firstname' ),
			'surname'		=> $this->request->get( 'surname' ),
			'email'			=> $this->request->get( 'email' ),
			'institution'	=> $this->request->get( 'institution' ),
		];

		$selectedGroups	= $this->request->get( 'groups' );
		if( !is_array( $selectedGroups ) )
			$selectedGroups	= [];

		$groups		= $this->logic->getGroups( [], ['title' => 'ASC'] );
		if( !$groups ){
			$this->messenger->noteNotice( 'Es ist noch keine Gruppe vorhanden. Weiterleitung zu den Gruppen.' );
			$this->restart( 'work/newsletter/group' );
		}
		foreach( $groups as $group ){
			if( $group->type == Model_Newsletter_Group::TYPE_AUTOMATIC )
				$selectedGroups[]	= $group->newsletterGroupId;
		}

		$this->addData( 'reader', $reader );
		$this->addData( 'groups', $groups );
		$this->addData( 'selectedGroups', $selectedGroups );

		$model		= new Model_Newsletter_Reader( $this->env );
		$totalReaders	= $model->count();
		if( $this->limiter && $this->limiter->denies( 'Work.Newsletter.Reader:maxItems', $totalReaders + 1 ) ){
			$this->messenger->noteNotice( 'Limit erreicht. Vorgang abgebrochen.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'totalReaders', $totalReaders );
	}

	/**
	 *	@param		int|string			$readerId
	 *	@param		int|string|NULL		$groupId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addGroup( int|string $readerId, int|string|NULL $groupId = NULL ): void
	{
		$groupId	= is_null( $groupId ) ? $this->request->get( 'groupId' ) : $groupId;
		if( $groupId )
			$this->logic->addReaderToGroup( $readerId, $groupId );
		$this->restart( 'edit/'.$readerId, TRUE );
	}

	/**
	 *	@param		int|string		$readerId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $readerId ): void
	{
		$words		= (object) $this->getWords( 'edit' );
		if( !$this->logic->checkReaderId( $readerId ) ){
			$this->messenger->noteError( $words->msgErrorInvalidId, $readerId );
			$this->restart( './work/newsletter/reader' );
		}
		if( $this->request->has( 'save' ) ){
			$this->logic->editReader( $readerId, $this->request->getAll() );
			$this->messenger->noteSuccess( $words->msgSuccess );
			$this->restart( './work/newsletter/reader/edit/'.$readerId );
		}
		$this->addData( 'readerId', $readerId );
		$this->addData( 'reader', $this->logic->getReader( $readerId ) );
		$this->addData( 'groups', $this->logic->getGroups( [], ['title' => 'ASC'] ) );
		$this->addData( 'readerGroups', $this->logic->getGroupsOfReader( $readerId, [], ['title' => 'ASC'] ) );
		$this->addData( 'readerLetters', $this->logic->getLettersOfReader( $readerId, ['status' => '>= 1'], ['title' => 'ASC'] ) );
	}

	/**
	 *	@param		string		$mode
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function export( string $mode = 'csv' ): void
	{
		if( $this->limiter && $this->limiter->denies( 'Work.Newsletter.Reader:allowExport' ) ){
			$this->messenger->noteNotice( 'Exportieren ist deaktiviert. Vorgang abgebrochen.' );
			$this->restart( NULL, TRUE );
		}

		$filterStatus		= $this->session->get( $this->filterPrefix.'status' );
		$filterEmail		= $this->session->get( $this->filterPrefix.'email' );
		$filterFirstname	= $this->session->get( $this->filterPrefix.'firstname' );
		$filterSurname		= $this->session->get( $this->filterPrefix.'surname' );
		$filterGroupId		= $this->session->get( $this->filterPrefix.'groupId' );
		$conditions	= [];
		if( strlen( $filterStatus ) )
			$conditions['status']	= $filterStatus;
		if( strlen( $filterEmail ) )
			$conditions['email']	= '%'.$filterEmail.'%';
		if( strlen( $filterFirstname ) )
			$conditions['firstname']	= '%'.$filterFirstname.'%';
		if( strlen( $filterSurname ) )
			$conditions['surname']	= '%'.$filterSurname.'%';
		if( strlen( $filterGroupId ) ){
			$readers	= [0];
			foreach( $this->logic->getReadersOfGroup( $filterGroupId ) as $reader )
				$readers[]	= $reader->newsletterReaderId;
			$conditions['newsletterReaderId']	= $readers;
		}
		$filterOrder	= ['email' => 'ASC'];

		$statuses		= [
			-2	=> 'deactivated',
			-1	=> 'unregistered',
			0	=> 'registered',
			1	=> 'confirmed',
		];

		$readers		= $this->logic->getReaders( $conditions, $filterOrder );
		switch( strtolower( $mode ) ){
			case 'list':
				$list	= [];
				foreach( $readers as $reader ){
					$receiver	= $reader->email;
					if( $reader->firstname && $reader->surname )
						$receiver	= '"'.$reader->firstname.' '.$reader->surname.'" <'.$receiver.'>';
					else if( $reader->firstname )
						$receiver	= '"'.$reader->firstname.'" <'.$receiver.'>';
					else if( $reader->surname )
						$receiver	= '"'.$reader->surname.'" <'.$receiver.'>';
					$list[]	= $receiver;
				}
				print( HtmlTag::create( 'xmp', join( ', ', $list ) ) );
				exit;
			case 'csv':
				$headers	= [
					'nr'			=> NULL,
					'id'			=> NULL,
					'email'			=> TRUE,
					'status'		=> TRUE,
					'gender'		=> TRUE,
					'prefix'		=> TRUE,
					'firstname'		=> TRUE,
					'surname'		=> TRUE,
					'institution'	=> TRUE,
					'groups'		=> TRUE,
					'registeredAt'	=> TRUE,
				];
				$data	= [join( ';', array_keys( $headers ) )];
				foreach( array_values( $readers ) as $nr => $reader ){
					$row	= [];
					foreach( $headers as $header => $toBeQuoted ){
						$value	= $reader->$header ?? '';
						if( $header === 'nr' )
							$value	= $nr + 1;
						else if( $header === 'id' )
							$value	= $reader->newsletterReaderId;
						else if( $header === 'registeredAt' )
							$value	= date( 'Y-m-d H:i:s', $reader->registeredAt );
						else if( $header == 'gender' )
		 					$value	= $reader->gender == 2 ? 'male' : 'female';
						else if( $header == 'status' )
		 					$row[]	= $statuses[$reader->status];
						else if( $header === 'groups' ){
							$list	= [];
							foreach( $this->logic->getGroupsOfReader( $reader->newsletterReaderId ) as $group )
								$list[]	= $group->title;
							$value	= join( ',', $list );
						}

						if( $toBeQuoted !== NULL )
							$value	= $toBeQuoted ? '"'.$value.'"' : $value;
						$row[]	= $value;
					}
					$data[]	= join( ';', $row );
				}
				$csv	= join( PHP_EOL, $data ).PHP_EOL;
				HttpDownload::sendString( $csv, "export_".date( "Y-m-d_H:i:s" ).".csv" );
		}
	}

	/**
	 *	@param		bool		$reset
	 *	@return		void
	 */
	public function filter( bool $reset = FALSE ): void
	{
		if( $reset ){
			foreach( $this->filters as $key )
				$this->session->remove( $this->filterPrefix.$key );
		}
		$this->session->remove( $this->filterPrefix.'limit' );
		$this->session->remove( $this->filterPrefix.'page' );
		if( $this->request->has( 'filter' ) ){
			foreach( $this->filters as $key )
				$this->session->set( $this->filterPrefix.$key, $this->request->get( $key ) );
		}
		$this->restart( './work/newsletter/reader' );
	}

	/**
	 *	@param		string		$mode
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function import( string $mode = 'csv' ): void
	{
		if( $this->limiter && $this->limiter->denies( 'Work.Newsletter.Reader:allowImport' ) ){
			$this->messenger->noteNotice( 'Importieren ist deaktiviert. Vorgang abgebrochen.' );
			$this->restart( NULL, TRUE );
		}
		$words		= (object) $this->getWords( 'add' );
		$groupId	= $this->request->get( 'groupId' );
		switch( strtolower( $mode ) ){
			case 'list':
				$parser		= new MailAddressCollectionParser();
				$list		= $parser->parse( $this->request->get( 'addresses' ) );
				foreach( $list as $entry ){
					$conditions	= ['email' => strtolower( $entry['address'] )];
					$existing	= $this->logic->getReaders( $conditions );
					if( $existing )
						$readerId	= $existing[0]->newsletterReaderId;
					else{
						$data	= [
							'status'		=> $this->request->get( 'status' ),
							'email'			=> $entry['address'],
							'surname'		=> $entry['fullname'],
							'firstname'		=> '',
							'gender'		=> '2',
						];
						$readerId	= $this->logic->addReader( $data );
					}
					if( $groupId )
						$this->logic->addReaderToGroup( $readerId, $groupId );
				}
				$this->messenger->noteSuccess( 'Added '.count( $list ).' readers to this group.' );
				break;
			case 'csv':
				$fileName	= 'import_'.date( 'Y-m-d:H:i:s' ).'.csv';
				$upload		= new Logic_Upload( $this->env );
				try{
					$upload->setUpload( $this->request->get( 'upload' ) );
					$upload->saveTo( $fileName );
					$reader	= new CsvFileReader( $fileName, TRUE );
					$csv	= $reader->toArray();
					foreach( $csv as $entry ){
						$conditions	= ['email' => strtolower( $entry['email'] )];
						$existing	= $this->logic->getReaders( $conditions );						//  get others by address
						if( $existing )																//  address is already existing
							$readerId	= $existing[0]->newsletterReaderId;							//  get ID of existing reader
						else																		//  new reader
							$readerId	= $this->logic->addReader( $entry );						//  add to database
						$this->logic->addReaderToGroup( $readerId, $groupId );						//  add reader to group
					}
					$this->messenger->noteSuccess( 'Added '.count( $csv ).' readers to this group.' );
				}
				catch( Exception $e ){
					$this->messenger->noteFailure( 'Error: '.$e->getMessage() );
				}
				break;
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		?int		$page
	 *	@return		void
	 */
	public function index( ?int $page = NULL ): void
	{
		if( is_null( $page ) ){
			$page	= 0;
//			if( $this->session->has( $this->filterPrefix.'page' ) )
//				$page	= $this->session->get( $this->filterPrefix.'page' );
		}

		/** @todo use count instead of getAll */
		$readers	= $this->logic->getReaders();
		$this->addData( 'total', count( $readers ) );

		$this->session->set( $this->filterPrefix.'page', $page );
		$filterStatus		= $this->session->get( $this->filterPrefix.'status' );
		$filterEmail		= $this->session->get( $this->filterPrefix.'email' );
		$filterFirstname	= $this->session->get( $this->filterPrefix.'firstname' );
		$filterSurname		= $this->session->get( $this->filterPrefix.'surname' );
		$filterGroupId		= $this->session->get( $this->filterPrefix.'groupId' );
		$filterLimit		= (int) $this->session->get( $this->filterPrefix.'limit' );
		$groups		= $this->logic->getGroups( [], ['title' => 'ASC'] );
		$conditions	= [];
		if( strlen( $filterStatus ) )
			$conditions['status']	= $filterStatus;
		if( strlen( $filterEmail ) )
			$conditions['email']	= '%'.$filterEmail.'%';
		if( strlen( $filterFirstname ) )
			$conditions['firstname']	= '%'.$filterFirstname.'%';
		if( strlen( $filterSurname ) )
			$conditions['surname']	= '%'.$filterSurname.'%';
		if( strlen( $filterGroupId ) ){
			$readers	= [0];
			foreach( $this->logic->getReadersOfGroup( $filterGroupId ) as $reader )
				$readers[]	= $reader->newsletterReaderId;
			$conditions['newsletterReaderId']	= $readers;
		}

		$filterOrder		= ['firstname' => 'ASC', 'surname' => 'ASC'];
		$filterOrder		= ['registeredAt' => 'DESC'];

		$limits		= [$page * $filterLimit, $filterLimit];
		$total		= count( $this->logic->getReaders( $conditions ) );
		$readers	= $this->logic->getReaders( $conditions, $filterOrder, $limits );
		$model		= new Model_Newsletter_Reader_Group( $this->env );
		foreach( $readers as $reader ){
			$conditions	= ['newsletterReaderId' => $reader->newsletterReaderId];
			$list		= [];
			foreach( $model->getAll( $conditions ) as $relation )
				$list[]	= $groups[$relation->newsletterGroupId];
			$reader->groups	= $list;
		}
		$this->addData( 'found', count( $readers ) );
//		$readers	= array_slice( $readers, $page * $filterLimit, $filterLimit );

		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterEmail', $filterEmail );
		$this->addData( 'filterFirstname', $filterFirstname );
		$this->addData( 'filterSurname', $filterSurname );
		$this->addData( 'filterGroupId', $filterGroupId );
		$this->addData( 'filterLimit', $filterLimit );
		$this->addData( 'filterPage', $page );

		$this->addData( 'readers', $readers );
		$this->addData( 'groups', $groups );

		$this->addData( 'totalReaders', $total );
	}

	/**
	 *	@param		int|string		$readerId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $readerId ): void
	{
		$words		= (object) $this->getWords( 'remove' );

/*		$readerLetters	= $this->logic->getLettersOfReader( $readerId,  ['status' => '>= 1'] );
		if( $readerLetters ){
			$this->messenger->noteError( $words->msgErrorReaderHasLetters );
			$this->restart( 'edit/'.$readerId, TRUE );
		}
*/		$this->logic->removeReader( $readerId );
		$this->messenger->noteSuccess( $words->msgSuccess );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string			$readerId
	 *	@param		int|string|NULL		$groupId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeGroup( int|string $readerId, int|string|NULL $groupId = NULL ): void
	{
		$groupId	= is_null( $groupId ) ? $this->request->get( 'groupId' ) : $groupId;
		$this->logic->removeReaderFromGroup( $readerId, $groupId );
		$this->restart( 'edit/'.$readerId, TRUE );
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->logic		= new Logic_Newsletter_Editor( $this->env );
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.work_newsletter.', TRUE );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'tabbedLinks', $this->moduleConfig->get( 'tabbedLinks' ) );
		if( $this->env->getModules()->has( 'Resource_Limiter' ) )
			$this->limiter	= Logic_Limiter::getInstance( $this->env );
		$this->addData( 'limiter', $this->limiter );

		if( $this->session->get( $this->filterPrefix.'limit' ) < 1 )
			$this->session->set( $this->filterPrefix.'limit', 10 );
	}
}
