<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Manage_My_Provision_License_Key extends Controller
{
	protected Messenger $messenger;
	protected Request $request;
	protected Dictionary $session;
	protected Logic_Authentication $logicAuth;
	protected Logic_User_Provision $logicProvision;
	protected string $filterPrefix		= 'filter_manage_my_provision_license_key_';

	public function filter( $reset = NULL ): void
	{
		$filters	= ['productId'];
		if( $reset ){
			foreach( $filters as $filter )
				$this->session->remove( $this->filterPrefix.$filter );
		}
		foreach( $filters as $filter )
			$this->session->set( $this->filterPrefix.$filter, $this->request->get( $filter ) );
		$this->restart( NULL, TRUE );
	}

	public function index(): void
	{
		$productId			= $this->session->get( $this->filterPrefix.'productId' );
		$userLicenseKeys	= $this->logicProvision->getUserLicenseKeysFromUser( $this->userId );

		$this->addData( 'userLicenseKeys', $userLicenseKeys );
	}

	public function view( string $userLicenseKeyId ): void
	{
		$userLicenseKey		= $this->logicProvision->getUserLicenseKey( $userLicenseKeyId );
		$userLicense		= $this->logicProvision->getUserLicense( $userLicenseKey->userLicenseId );
		$product			= $this->logicProvision->getProduct( $userLicense->productId );

		$this->addData( 'product', $product );
		$this->addData( 'userLicenseKey', $userLicenseKey );
		$this->addData( 'userLicense', $userLicense );
	}

	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicProvision	= Logic_User_Provision::getInstance( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicAuth		= Logic_Authentication::getInstance( $this->env );
		$this->userId			= $this->logicAuth->getCurrentUserId();
		$this->products			= $this->logicProvision->getProducts( 1 );

		if( count( $this->products ) == 1 ){
			$productId	= $this->products[0]->productId;
			$this->session->set( $this->filterPrefix.'productId', $productId );
		}
		$this->addData( 'products', $this->products );
		$this->addData( 'filterProductId', $this->session->get( $this->filterPrefix.'productId' ) );
	}
}
