<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Company extends Controller
{
	protected $modelBranch;
	protected $modelCompany;

	public function index( $companyId = NULL )
	{
		if( $companyId !== NULL && strlen( trim( $companyId ) ) && (int) $companyId > 0 ){
			$this->restart( 'view/'.$companyId );
		}
		$companies	= $this->modelCompany->getAll();
		$this->addData( 'companies', $companies );
	}

	public function view( $companyId )
	{
		$companyId	= (int) $companyId;

		$company    = $this->modelCompany->get( $companyId );
		if( !$company ){
			$this->messenger->noteError( "Das aufgerufene Unternehmen existiert nicht." );
			$this->restart();
		}
		$branches	= $this->modelBranch->getAllByIndex( 'companyId', $companyId );

		$this->addData( 'company', $company );
		$this->addData( 'branches', $branches );
		$this->addData( 'companyId', $companyId );
	}

	protected function __onInit()
	{
		$this->modelBranch	= new Model_Branch( $this->env );
		$this->modelCompany	= new Model_Company( $this->env );
	}
}
