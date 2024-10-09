<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Customer_Project extends Controller
{
	protected $messenger;
	protected Model_Customer_Project $modelCustomer;
	protected Logic_CustomerProject $logic;

	public function add( int|string $customerId ): void
	{
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

	public function index( $customerId )
	{

		$relations	= $this->logic->getProjects( $customerId );

		$logic		= Logic_Project::getInstance( $this->env );
		$list		= [];
		$projects	= $logic->getProjects( ['status' => [1, 2, 3, 4, 5]] );
		foreach( $projects as $project ){
			if( !array_key_exists( $project->projectId, $relations ) )
				$list[]	= $project;
		}
		$this->addData( 'relations', $relations );
		$this->addData( 'customerId', $customerId );
		$this->addData( 'customer', $this->modelCustomer->get( $customerId ) );
		$this->addData( 'projects', $list );
	}

	public function remove( $customerId, $projectId )
	{
		if( $this->logic->remove( $customerId, $projectId ) )
			$this->messenger->noteSuccess( 'Relation removed.' );
		$this->restart( $customerId, TRUE );
	}

	protected function __onInit(): void
	{
		$this->messenger		= $this->env->getMessenger();
		$this->modelCustomer	= new Model_Customer( $this->env );
		$this->addData( 'useRatings', $this->env->getModules()->has( 'Manage_Customer_Rating' ) );
		$this->addData( 'useMap', $this->env->getModules()->has( 'UI_Map' ) );
		$this->logic	= Logic_CustomerProject::getInstance( $this->env );
	}
}
