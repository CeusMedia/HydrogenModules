<?php
class Logic_CustomerProject{

	protected $env;
	protected static $instance		= NULL;
	protected $modelCustomer;
	protected $modelProject;
	protected $modelRelation;

	protected function __construct( $env ){
		$this->env	= $env;
		$this->modelCustomer	= new Model_Customer( $env );
		$this->modelProject		= new Model_Project( $env );
		$this->modelRelation	= new Model_Customer_Project( $env );
	}

	protected function __clone(){
	}

	public static function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new Logic_CustomerProject( $env );
		return self::$instance;
	}

	public function add( $customerId, $projectId, $type ){
		$session	= $this->env->getSession();
		return $this->modelRelation->add( array(
			'customerId'	=> $customerId,
			'projectId'		=> $projectId,
			'userId'		=> $session->get( 'userId' ),
			'type'			=> $type,
			'status'		=> 1,
			'createdAt'		=> time(),
		) );
	}

	public function getProjects( $customerId ){
		$list		= array();
		$relations	= $this->modelRelation->getAll( array( 'customerId' => $customerId ) );
		foreach( $relations as $relation ){
			$relation->project	= $this->modelProject->get( $relation->projectId );
			$list[$relation->projectId]	= $relation;
		}
		return $list;
	}

	public function remove( $customerId, $projectId ){
		$relations	= $this->modelRelation->getAll( array( 'customerId' => $customerId, 'projectId' => $projectId ) );
		foreach( $relations as $relation )
			$this->modelRelation->remove( $relation->customerProjectId );
		return count( $relations );
	}
}
?>
