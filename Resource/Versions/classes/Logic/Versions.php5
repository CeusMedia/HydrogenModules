<?php
class Logic_Versions{

	protected $env;
	static protected $instance;
	protected $model;
	protected $userId;

	protected function __construct( $env ){
		$this->env	= $env;
		$this->model	= new Model_Version( $env );
		$this->detectUserId();
	}

	protected function __clone(){}

	public function add( $module, $id, $content, $authorId = NULL ){
		$data		= array(
			'userId'	=> $authorId ? $authorId : $this->userId,
			'module'	=> $module,
			'id'		=> $id,
			'version'	=> $this->getNextVersionNr( $module, $id ),
			'timestamp'	=> time(),
		);
		$versionId	= $this->model->add( $data );
		$data		= array( 'content' => $content );
		$this->model->edit( $versionId, $data, FALSE );
	}

	public function detectUserId(){
		if( $this->env->getModules()->has( 'Resource_Authentication' ) ){
			$logic			= Logic_Auth::getInstance( $this->env );
			$this->userId	= $logic->getCurrentUserId();
			return TRUE;
		}
		return FALSE;
	}

	public function get( $module, $id, $version = NULL ){
		if( !is_null( $version ) ){
			$conditions = array(
				'module'	=> $module,
				'id'		=> $id,
				'version'	=> $version,
			);
			return $this->model->get( $conditions );
		}
		$conditions	= array(
			'module'	=> $module,
			'id'		=> $id,
		);
		return $this->get( $conditions, array( 'version' => 'DESC' ) );
	}

	public function getAll( $module, $id, $conditions = array(), $orders = array(), $limits = array() ){
		$indices	= array(
			'module'	=> $module,
			'id'		=> $id,
		);
		$conditions	= array_merge( $conditions, $indices );
		return $this->model->getAll( $conditions, $orders, $limits );
	}

	public function getById( $versionId ){
		return $this->model->get( $versionId );
	}

	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new self( $env );
		return self::$instance;
	}

	protected function getNextVersionNr( $module, $id ){
		$conditions	= array(
			'module'	=> $module,
			'id'		=> $id,
		);
		$orders		= array( 'version' => 'DESC' );
		$latest		= $this->model->get( $conditions, $orders );
		if( $latest )
			return (int) $latest->version + 1;
		return 0;
	}

	public function has( $module, $id, $version = NULL ){
		if( !is_null( $version ) )
			return (bool) $this->get( $module, $id, $version );
		return count( $this->getAll( $module, $id ) ) > 0;
	}

	public function hasById( $versionId ){
		return (bool) $this->getById( $versionId );
	}

	public function remove( $module, $id, $version ){
		$entry	= $this->get( $module, $id, $version );
		if( !$entry )
			return FALSE;
		$this->model->remove( $entry->versionId );
	}

/*	public function set( $versionId, $content, $data ){

	}*/
}
