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

	public function getProjects( $customerId ){
		$list		= array();
		$relations	= $this->modelRelation->getAll( array( 'customerId' => $customerId ) );
		foreach( $relations as $relation ){
			$list[$relation->projectId]	= $this->modelProject->get( $relation->projectId );
		}
		return $list;
	}
}
?>