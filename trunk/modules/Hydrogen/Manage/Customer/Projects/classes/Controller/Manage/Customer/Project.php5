<?php
class Controller_Manage_Customer_Project extends CMF_Hydrogen_Controller{

	protected $messenger;
	protected $modelCustomer;
	protected $logic;

	public function __onInit(){
		$this->messenger		= $this->env->getMessenger();
		$this->modelCustomer	= new Model_Customer( $this->env );
		$this->addData( 'useRatings', $this->env->getModules()->has( 'Manage_Customer_Rating' ) );
		$this->addData( 'useMap', $this->env->getModules()->has( 'UI_Map' ) );
		$this->logic	= Logic_CustomerProject::getInstance( $this->env );
	}

	public function add( $customerId ){
		$request	= $this->env->getRequest();
		$projectId	= $request->get( 'projectId' );
		$type		= $request->get( 'type' );
		$customer	= $this->modelCustomer->get( $customerId );
		if( !$customer ){
			$this->messenger->noteError( 'Invalid customer ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->logic->add( $customerId, $projectId, $type );
		$this->messenger->noteSuccess( 'Relation added.' );
		$this->restart( $customerId, TRUE );
	}

	public function index( $customerId ){

		$relations	= $this->logic->getProjects( $customerId );

		$logic		= new Logic_Project( $this->env );
		$list		= array();
		$projects	= $logic->getProjects( array( 'status' => array( 1, 2, 3, 4, 5 ) ) );
		foreach( $projects as $project ){
			if( !array_key_exists( $project->projectId, $relations ) )
				$list[]	= $project;
		}
		$this->addData( 'relations', $relations );
		$this->addData( 'customerId', $customerId );
		$this->addData( 'customer', $this->modelCustomer->get( $customerId ) );
		$this->addData( 'projects', $list );
	}

	public function remove( $customerId, $projectId ){
		if( $this->logic->remove( $customerId, $projectId ) )
			$this->messenger->noteSuccess( 'Relation removed.' );
		$this->restart( $customerId, TRUE );
	}
}
?>
