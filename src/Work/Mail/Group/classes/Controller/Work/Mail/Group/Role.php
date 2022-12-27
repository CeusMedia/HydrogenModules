<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Work_Mail_Group_Role extends Controller
{
	protected Dictionary $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected Model_Mail_Group_Role $modelRole;

	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			$title	= trim( $this->request->get( 'title' ) );
			$this->modelRole->add( [
				'status'		=> $this->request->get( 'status' ),
				'rank'			=> $this->request->get( 'rank' ),
				'read'			=> $this->request->get( 'read' ),
				'write'			=> $this->request->get( 'write' ),
				'title'			=> $title,
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			] );
			$this->restart( NULL, TRUE );
		}
		$role	= [];
		foreach( $this->modelRole->getColumns() as $column )
			$role[$column]	= $this->request->get( $column );
		$this->addData( 'role', (object) $role );
	}

	public function checkId( $roleId, bool $strict = TRUE )
	{
		$role	= $this->modelRole->get( $roleId );
		if( $role )
			return $role;
		if( $strict )
			throw new RangeException( 'Invalid role ID: '.$roleId );
		return NULL;
	}

	public function edit( $roleId ): void
	{
		$role	= $this->checkId( $roleId );
		if( $this->request->has( 'save' ) ){
			$title	= trim( $this->request->get( 'title' ) );
			$this->modelRole->edit( $roleId, [
				'status'		=> $this->request->get( 'status' ),
				'rank'			=> $this->request->get( 'rank' ),
				'read'			=> $this->request->has( 'read' ) ? 1 : 0,
				'write'			=> $this->request->has( 'write' ) ? 1 : 0,
				'modifiedAt'	=> time(),
			] );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'role', $role );
	}

	public function index(): void
	{
		$indices	= [];
		$orders		= ['title' => 'ASC'];
		$limits		= [];
		$roles		= $this->modelRole->getAll( $indices, $orders,$limits );
		$this->addData( 'roles', $roles );
	}

	public function remove( $roleId ): void
	{
		$role	= $this->checkId( $roleId );
		if( $role ){
			$this->modelRole->remove( $roleId );
			$this->restart( NULL, TRUE );
		}
	}

	public function setStatus( $roleId, $status ): void
	{
		$role	= $this->checkId( $roleId );
		if( $role ){
			$this->modelRole->edit( $roleId, [
				'status'		=> (int) $status,
				'modifiedAt'	=> time(),
			] );
		}
	}

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->modelRole	= new Model_Mail_Group_Role( $this->env );
	}
}
