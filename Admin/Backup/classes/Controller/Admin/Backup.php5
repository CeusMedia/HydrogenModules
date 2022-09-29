<?php

use CeusMedia\Common\FS\Folder;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Admin_Backup extends Controller
{
	protected $request;
	protected $session;
	protected $messenger;
	protected $model;
	protected $moduleConfig;
	protected $pathFiles;
	protected $filterPrefix				= 'filter_admin_backup_';
	protected $defaultLimit				= 10;
	protected $defaultOrderColumn		= 'createdAt';
	protected $defaultOrderDirection	= 'DESC';
	protected $filters					= [];

	public function filter( $reset = NULL )
	{
		if( $reset ){
			foreach( $this->filters as $filterKey )
				$this->session->remove( $this->filterPrefix.$filterKey );
			$this->session->remove( $this->filterPrefix.'page' );
			$this->session->remove( $this->filterPrefix.'limit' );
		}
		foreach( $this->filters as $filterKey ){
			if( $this->request->has( $filterKey ) ){
				$filterValue = $this->request->get( $filterKey );
				$this->session->set( $this->filterPrefix.$filterKey, $filterValue );
			}
		}
		$this->session->set( $this->filterPrefix.'page', 0);
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 )
	{
		$conditions	= [];
		$filters	= $this->session->getAll( $this->filterPrefix, TRUE );
		if( $filters->has( 'aaa' ) )
			$conditions['aaa']	= $filters->get( 'aaa' );
		$count	= $this->model->count( $conditions );
		$limit	= $filters->get( 'limit' );
		$page	= is_null( $page ) ? $filters->get( 'page' ) : 0;
		if( $page > 0 && $page > floor( $count / $limit ) )
			$page	= 0;

		$orders		= ['createdAt' => 'DESC'];
		$limits		= [$page * $limit, $limit];
		$backups	= $this->model->getAll( $conditions, $orders, $limits );
		$this->addData( 'nrFound', $count );
		$this->addData( 'filterPage', $page );
		$this->addData( 'filterLimit', $limit );
		$this->addData( 'backups', $backups );
	}

	public function add()
	{
		$this->messenger->noteFailure( 'Not implemented, yet' );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	...
	 *	@param		string		$backupId
	 *	@return		void
	 *	@todo		implement
	 */
	public function restore( string $backupId )
	{
		$this->messenger->noteFailure( 'Not implemented, yet' );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	...
	 *	@param		string		$backupId
	 *	@return		void
	 *	@todo		implement
	 */
	public function remove( string $backupId )
	{
		$this->messenger->noteFailure( 'Not implemented, yet' );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit()
	{
		$this->moduleConfig = $this->env->getConfig()->getAll('module.admin_backup.', TRUE);
		$this->pathFiles = $this->moduleConfig->get('path');
		$this->model = new Model_Backup($this->env);
		if (!file_exists($this->pathFiles))
			new Folder( $this->pathFiles, TRUE );
		if( !$this->session->has( $this->filterPrefix.'limit' ) )
			$this->session->set( $this->filterPrefix.'limit', $this->defaultLimit );
		if( !$this->session->has( $this->filterPrefix.'orderColumn' ) ){
			$this->session->set( $this->filterPrefix.'orderColumn', $this->defaultOrderColumn );
			$this->session->set( $this->filterPrefix.'orderDirection', $this->defaultOrderDirection );
		}
	}
}
