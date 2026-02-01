<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\PartitionSession;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Work_Newsletter_Group extends Controller
{
	/**	@var	Logic_Newsletter_Editor		$logic 		Instance of newsletter editor logic */
	protected Logic_Newsletter_Editor $logic;
	protected PartitionSession $session;
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Dictionary $moduleConfig;
	protected ?Logic_Limiter $limiter			= NULL;
	protected bool $useUserGroupRelations		= FALSE;
	protected bool $allowToCopyUsersFromGroups	= FALSE;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function add(): void
	{
		$words		= (object) $this->getWords( 'add' );
		if( $this->request->has( 'save' ) ){
			$groupId	= $this->logic->addGroup( $this->request->getAll() );
			$this->messenger->noteSuccess( $words->msgSuccess );
			$this->onAddCopyReadersOfFormerGroups( $groupId );
			$this->onAddSetGroupRelations( $groupId );
			$this->restart( './work/newsletter/group/edit/'.$groupId );
		}
		$group	= (object) [
			'title'		=> $this->request->get( 'title' ),
			'type'		=> $this->request->get( 'type' ),
		];
		$this->addData( 'group', $group );

		//  former group to copy
		$groups	= [];
		if( $this->allowToCopyUsersFromGroups ){
			$conditions	= ['type' => [0, 2]];
			$groups	= $this->logic->getGroups( $conditions, ['title' => 'ASC'] );
			foreach( $groups as $group )
				$group->count	= $this->logic->countGroupReaders( $group->newsletterGroupId );
		}
		$this->addData( 'groups', $groups );

		$model		= new Model_Newsletter_Group( $this->env );
		$totalGroups	= $model->count();
		if( $this->limiter && $this->limiter->denies( 'Work.Newsletter.Group:maxItems', $totalGroups + 1 ) ){
			$this->messenger->noteNotice( 'Limit erreicht. Vorgang abgebrochen.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'totalGroups', $totalGroups );
	}

	/**
	 *	@param		int|string		$groupId
	 *	@return		void
	 *	@throws		InvalidArgumentException		if newsletter group ID is invalid
	 */
	public function edit( int|string $groupId ): void
	{
		$words		= (object) $this->getWords( 'edit' );
		if( !$this->logic->checkGroupId( $groupId ) || !$this->logic->hasGroupAccessToNewsletterGroup( $groupId ) ){
			$this->messenger->noteError( $words->msgErrorInvalidId, $groupId );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->has( 'save' ) ){
			$this->logic->editGroup( $groupId, $this->request->getAll() );
			$this->messenger->noteSuccess( $words->msgSuccess );
			$this->restart( './work/newsletter/group/edit/'.$groupId );
		}
		$this->addData( 'groupId', $groupId );
		$this->addData( 'group', $this->logic->getGroup( $groupId ) );

		$orders		= ['firstname' => 'ASC', 'surname' => 'ASC'];
		$readers	= $this->logic->getReadersOfGroup( $groupId, [], $orders );
		$this->addData( 'groupReaders', $readers );

		$this->addData( 'canManageGroupRelations', $this->env->getAcl()->has( 'manage/group', 'relate' ) );
		$this->addData( 'canExport', $this->env->getAcl()->has( 'manage/group', 'export' ) );
		$this->addData( 'canImport', $this->env->getAcl()->has( 'manage/group', 'import' ) );
		$this->addData( 'canRemove', $this->env->getAcl()->has( 'manage/group', 'remove' ) );

	}

	/**
	 *	@param		int|string		$groupId
	 *	@return		never
	 */
	public function export( int|string $groupId ): never
	{
		$conditions	= ['status' => '1'];
		$orders		= ['firstname' => 'ASC', 'surname' => 'ASC'];
		$readers	= $this->logic->getReadersOfGroup( $groupId, $conditions, $orders );
		$list		= [];
		foreach( $readers as $reader )
			$list[]	= $reader->email;
		header( 'Content-Type: text/plain; charset=utf8' );
		print( join( "; ", $list ) );
		exit;
	}

	/**
	 *	@param		$reset
	 *	@return		void
	 */
	public function filter( $reset = NULL ): void
	{
		if( $reset ){
			$this->session->remove( 'filter_work_newsletter_group_query' );
			$this->session->remove( 'filter_work_newsletter_group_status' );
			$this->session->remove( 'filter_work_newsletter_group_sort' );
			$this->session->remove( 'filter_work_newsletter_group_direction' );
			$this->session->remove( 'filter_work_newsletter_group_limit' );
		}
		$this->session->set( 'filter_work_newsletter_group_query', $this->request->get( 'query' ) );
		$this->session->set( 'filter_work_newsletter_group_status', $this->request->get( 'status' ) );
		$this->session->set( 'filter_work_newsletter_group_sort', $this->request->get( 'sort' ) );
		$this->session->set( 'filter_work_newsletter_group_direction', $this->request->get( 'direction' ) );
		$this->session->set( 'filter_work_newsletter_group_limit', $this->request->get( 'limit' ) );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@todo		finish implementation: Semco / Custom CSV Data Mapping Strategies 
	 */
	public function import(): void
	{
		if( $this->limiter && $this->limiter->denies( 'Work.Newsletter.Group:allowImport' ) ){
			$this->messenger->noteNotice( 'Importieren ist deaktiviert. Vorgang abgebrochen.' );
			$this->restart( NULL, TRUE );
		}
		$groupId = $this->request->get( 'groupId' );


		$fileName	= 'import_newsletter_group_'.$groupId.'_'.date( 'Y-m-d:H:i:s' ).'.csv';
		$upload		= new Logic_Upload( $this->env );
		try{
			$upload->setUpload( $this->request->get( 'upload' ) );
			$upload->saveTo( $fileName );
			$reader	= new CsvFileReader( $fileName, TRUE );
			$csv	= $reader->toArray();

			//  @todo !!! add Semco CSV Mappper here !!!

			foreach( $csv as $entry ){
				$conditions	= ['email' => strtolower( $entry['email'] )];
				$existing	= $this->logic->getReaders( $conditions );						//  get others by address
				if( $existing )																//  address is already existing
					$readerId	= $existing[0]->newsletterReaderId;							//  get ID of existing reader
				else{																		//  new reader
					try{
						$readerId	= $this->logic->addReader( $entry );						//  add to database
					}
					catch( Throwable $e ){
						$this->messenger->noteError( 'Fehler beim Import: '.$e->getMessage() );
						$this->restart( './work/newsletter/group/edit/'.$groupId );
					}
				}
				$this->logic->addReaderToGroup( $readerId, $groupId );						//  add reader to group
			}
			$this->messenger->noteSuccess( 'Added '.count( $csv ).' readers to this group.' );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( 'Error: '.$e->getMessage() );
		}
		$this->restart( './work/newsletter/group/edit/'.$groupId );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function index(): void
	{
		$orders		= ['title' => 'ASC'];

		$filterQuery	= $this->session->get( 'filter_work_newsletter_group_query', '' );
		$filterStatus	= $this->session->get( 'filter_work_newsletter_group_status', '' );

		$conditions		= [];
		if( $filterQuery )
			$conditions['title']	= '%'.$filterQuery.'%';
		if( '' !== $filterStatus )
			$conditions['status']	= $filterStatus;

		$groups		= $this->logic->getGroups( $conditions, $orders );
		foreach( $groups as $group )
			$group->readers	= $this->logic->getGroupReaders( $group->newsletterGroupId );
		$this->addData( 'groups', $groups );
		$this->addData( 'filterQuery', $filterQuery );
		$this->addData( 'filterStatus', $filterStatus );

		$model		= new Model_Newsletter_Group( $this->env );
		$this->addData( 'totalGroups', $model->count() );
	}

	/**
	 *	@param		int|string		$groupId
	 *	@return		void
	 */
	public function remove( int|string $groupId ): void
	{
		if( !$this->logic->hasGroupAccessToNewsletterGroup( $groupId ) )
			return;
			
		$words		= (object) $this->getWords( 'remove' );
		$this->logic->removeGroup( $groupId );
		$this->messenger->noteSuccess( $words->msgSuccess );
		$this->restart( NULL,  TRUE );
	}

	/**
	 *	@param		int|string			$groupId
	 *	@param		int|string|NULL		$readerId
	 *	@return		void
	 */
	public function removeReader( int|string $groupId, int|string|NULL $readerId = NULL ): void
	{
		$readerId	= is_null( $readerId ) ? $this->request->get( 'readerId' ) : $readerId;
		$this->logic->removeReaderFromGroup( $readerId, $groupId );
		$this->restart( 'edit/'.$groupId, TRUE );
	}

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

		$this->useUserGroupRelations		= $this->moduleConfig->get( 'useUserGroupRelations', FALSE );
		$this->allowToCopyUsersFromGroups	= $this->moduleConfig->get( 'group.allowToCopyUsersFromGroups', FALSE );
		$this->addData( 'useUserGroupRelations', $this->useUserGroupRelations );
		$this->addData( 'allowToCopyUsersFromGroups', $this->allowToCopyUsersFromGroups );
	}

	protected function onAddCopyReadersOfFormerGroups( int|string $groupId ): void
	{
		if( !$this->allowToCopyUsersFromGroups )
			return;

		$copyUsersOfGroupIds	= $this->request->get( 'copyUsersOfGroupIds' );
		if( is_array( $copyUsersOfGroupIds ) ){
			$readerIds	= [];
			foreach( $copyUsersOfGroupIds as $copyGroupId ){
				foreach( $this->logic->getGroupReaders( $copyGroupId ) as $reader ){
					if( !in_array( $reader->newsletterReaderId, $readerIds ) ){
						$readerIds[]	= $reader->newsletterReaderId;
						$this->logic->addReaderToGroup( $reader->newsletterReaderId, $groupId );
					}
				}
			}
			if( 0 !== count( $readerIds ) ){
				$words = (object) $this->getWords( 'add' );
				$this->messenger->noteNotice( $words->msgGroupUsersImported, count( $readerIds ) );
			}
		}
	}

	/**
	 *	On adding a group, set relation between groups and newsletter groups.
	 *	Takes list of newsletter group IDs from request pair "relationGroupIds".
	 *	Sets relation for module key "Resource_Newsletter.Group".
	 *	@param		int|string		$groupId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function onAddSetGroupRelations( int|string $groupId ): void
	{
		if( !$this->useUserGroupRelations )
			return;

//		if( !$this->request->get( 'useGroupRelations' ) )
//			return;

		$logicRelation		= Logic_GroupRelation::getInstance( $this->env );
		$relatedGroupIds	= $this->request->get( 'relationGroupIds' );
		foreach( $relatedGroupIds as $relatedGroupId )
			$logicRelation->addModuleEntityRelation( $groupId, 'Resource_Newsletter.Group', $relatedGroupId );
	}
}
