<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Company_Branch extends Controller
{
	protected $messenger;
	protected $modelBranch;
	protected $modelCompany;

	public function index( $branchId = NULL )
	{
		if( $branchId !== NULL && strlen( trim( $branchId ) ) && (int) $branchId > 0 ){
			$this->restart( 'view/'.$branchId, TRUE );
		}
		else
			$this->restart( './company', FALSE, 301 );
	}

	public function view( $branchId )
	{
		$branchId = (int) $branchId;

		$branch		= $this->modelBranch->get( $branchId );
		if( !$branch ){
			$this->messenger->noteError( "Die aufgerufene Niederlassung existiert nicht." );
			$this->restart( './company' );
		}
		$company	= $this->modelCompany->get( $branch->companyId );
		if( !$company ){
			$this->messenger->noteError( "Das aufgerufene Unternehmen existiert nicht." );
			$this->restart();
		}

		$branches	= $this->modelBranch->getAllByIndex( 'companyId', $branch->companyId );

		$this->addData( 'branch', $branch );
		$this->addData( 'branches', $branches );
		$this->addData( 'company', $company );
		$this->addData( 'branchId', $branchId );
	}

	protected function __onInit()
	{
		$this->messenger	= $this->env->getMessenger();
		$this->modelBranch	= new Model_Branch( $this->env );
		$this->modelCompany	= new Model_Company( $this->env );
	}
}
