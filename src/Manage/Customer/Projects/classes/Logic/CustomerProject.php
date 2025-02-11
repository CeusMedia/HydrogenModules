<?php

use CeusMedia\HydrogenFramework\Environment;

class Logic_CustomerProject
{
	protected Environment $env;
	protected static ?self $instance		= NULL;
	protected Model_Customer $modelCustomer;
	protected Model_Project $modelProject;
	protected Model_Customer_Project $modelRelation;

	public static function getInstance( Environment $env ): self
	{
		if( !self::$instance )
			self::$instance	= new Logic_CustomerProject( $env );
		return self::$instance;
	}

	public function add( int|string $customerId, $projectId, $type ): int|string
	{
		$session	= $this->env->getSession();
		return $this->modelRelation->add( [
			'customerId'	=> $customerId,
			'projectId'		=> $projectId,
			'userId'		=> $session->get( 'auth_user_id' ),
			'type'			=> $type,
			'status'		=> 1,
			'createdAt'		=> time(),
		] );
	}

	public function getProjects( int|string $customerId ): array
	{
		$list		= [];
		$relations	= $this->modelRelation->getAll( ['customerId' => $customerId] );
		foreach( $relations as $relation ){
			$relation->project	= $this->modelProject->get( $relation->projectId );
			$list[$relation->projectId]	= $relation;
		}
		return $list;
	}

	public function remove( int|string $customerId, int|string $projectId ): ?int
	{
		$relations	= $this->modelRelation->getAll( ['customerId' => $customerId, 'projectId' => $projectId] );
		foreach( $relations as $relation )
			$this->modelRelation->remove( $relation->customerProjectId );
		return count( $relations );
	}

	protected function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->modelCustomer	= new Model_Customer( $env );
		$this->modelProject		= new Model_Project( $env );
		$this->modelRelation	= new Model_Customer_Project( $env );
	}

	protected function __clone()
	{
	}
}
