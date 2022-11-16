<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Newsletter_Group extends Controller
{
	/**	@var	Logic_Newsletter_Editor		$logic 		Instance of newsletter editor logic */
	protected $logic;
	protected $session;
	protected $request;
	protected $messenger;
	protected Dictionary $moduleConfig;
	protected $limiter;

	public function add()
	{
		$words		= (object) $this->getWords( 'add' );
		if( $this->request->has( 'save' ) ){
			$groupId	= $this->logic->addGroup( $this->request->getAll() );
			$this->messenger->noteSuccess( $words->msgSuccess );
			$copyUsersOfGroupIds	= $this->request->get( 'copyUsersOfGroupIds' );
			if( is_array( $copyUsersOfGroupIds ) && count( $copyUsersOfGroupIds ) ){
				$readerIds	= [];
				foreach( $this->request->get( 'copyUsersOfGroupIds' ) as $copyGroupId ){
					foreach( $this->logic->getGroupReaders( $copyGroupId ) as $reader ){
						if( !in_array( $reader->newsletterReaderId, $readerIds ) ){
							$readerIds[]	= $reader->newsletterReaderId;
							$this->logic->addReaderToGroup( $reader->newsletterReaderId, $groupId );
						}
					}
				}
				$this->messenger->noteNotice( $words->msgGroupUsersImported, count( $readerIds ) );
			}
			$this->restart( './work/newsletter/group/edit/'.$groupId );
		}
		$group	= (object) array(
			'title'		=> $this->request->get( 'title' ),
			'type'		=> $this->request->get( 'type' ),
		);
		$this->addData( 'group', $group );
		$groups	= $this->logic->getGroups( ['type' => [0, 2]], ['title' => 'ASC'] );
		foreach( $groups as $group )
			$group->count	= $this->logic->countGroupReaders( $group->newsletterGroupId );
		$this->addData( 'groups', $groups );

		$model		= new Model_Newsletter_Group( $this->env );
		$totalGroups	= $model->count();
		if( $this->limiter && $this->limiter->denies( 'Work.Newsletter.Group:maxItems', $totalGroups + 1 ) ){
			$this->messenger->noteNotice( 'Limit erreicht. Vorgang abgebrochen.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'totalGroups', $totalGroups );
	}

	public function edit( $groupId )
	{
		$words		= (object) $this->getWords( 'edit' );
		if( !$this->logic->checkGroupId( $groupId ) ){
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
	}

	public function export( $groupId )
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

	public function filter( $reset = NULL )
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

	public function index()
	{
		$orders		= ['title' => 'ASC'];

		$filterQuery	= $this->session->get( 'filter_work_newsletter_group_query' );
		$filterStatus	= $this->session->get( 'filter_work_newsletter_group_status' );

		$conditions		= [];
		if( $filterQuery )
			$conditions['title']	= '%'.$filterQuery.'%';
		if( strlen( $filterStatus ) )
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

	public function remove( $groupId )
	{
		$words		= (object) $this->getWords( 'remove' );
		$this->logic->removeGroup( $groupId );
		$this->messenger->noteSuccess( $words->msgSuccess );
		$this->restart( NULL,  TRUE );
	}

	public function removeReader( $groupId, $readerId )
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
	}
}
