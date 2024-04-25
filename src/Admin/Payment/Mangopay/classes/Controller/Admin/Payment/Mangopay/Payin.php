<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_Payment_Mangopay_Payin extends Controller
{
	protected Request $request;
	protected MessengerResource $messenger;
	protected Logic_Payment_Mangopay $mangopay;
	protected Model_Mangopay_Payin $model;
	protected Dictionary $modulConfig;

	public function index( $page = 0 )
	{
		$limit		= 15;
		$offset		= $page * $limit;
		$conditions	= [];
		$orders		= ['modifiedAt' => 'DESC'];
		$limits		= [$offset, $limit];
		$total		= $this->model->count();
		$count		= $this->model->count( $conditions );
		$pages		= ceil( $count / $limit );
		$payins		= $this->model->getAll( $conditions, $orders, $limits );

		foreach( $payins as $nr => $payin ){
			if( $payin->userId )
				$payin->user	= $this->mangopay->getUser( $payin->userId );
		}
		$this->addData( 'payins', $payins );
		$this->addData( 'count', $count );
		$this->addData( 'pages', $pages );
		$this->addData( 'page', $page );
	}

	public function view( $payinId ): void
	{
		$payin	= $this->model->get( $payinId );
		if( !$payin ){
			$this->messenger->noteError( 'Invalid payin ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'payin', $payin );
//		$this->addData( 'page', $this->request->get( 'page' ) );
	}

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->mangopay		= Logic_Payment_Mangopay::getInstance( $this->env );
		$this->model		= new Model_Mangopay_Payin( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
	}
}
