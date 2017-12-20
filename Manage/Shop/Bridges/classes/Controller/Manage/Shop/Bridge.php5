<?php
class Controller_Manage_Shop_Bridge extends CMF_Hydrogen_Controller{

	protected $bridges	= array();
	protected $logicBridge;
	protected $messenger;
	protected $model;
	protected $request;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_Shop_Bridge( $this->env );
		$this->logicBridge	= new Logic_ShopBridge( $this->env );
		foreach( $this->logicBridge->getBridges() as $bridge ){
			$bridge->data->status = 1;
			if( $bridge->status < 0 )
				$bridge->data->status = -2;
			$this->bridges[$bridge->data->bridgeId]	= $bridge->data;

		}
//        foreach( $this->model->getAll() as $bridge )
//			$this->bridges[$bridge->bridgeId]	= $bridge;
		$this->addData( 'bridges', $this->bridges );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$data['createdAt']	= time();
			$bridgeId	= $this->model->add( $data );
			$this->messenger->noteSuccess( 'Bridge added.' );
			$this->restart( NULL, TRUE );
		}
		$data	= array();
		foreach( $this->model->getColumns() as $column ){
			$data[$column]	= $this->request->get( $column );
		}
		$this->addData( 'bridge', (object) $data );
	}

	public function edit( $bridgeId ){
		$bridge	= $this->model->get( $bridgeId );
		if( !$bridge ){
			$this->messenger->noteError( 'Invalid bridge ID.' );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->has( 'save' ) ){
			$data		= $this->request->getAll();
			$this->model->edit( $bridgeId, $data );
			$this->messenger->noteSuccess( 'Bridge saved.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'bridge', $bridge );
		$this->addData( 'bridgeId', $bridgeId );
	}

	public function index(){
//		print_m( $this->logicBridge );
//		$this->addData( 'bridges', $this->logicBridge->getSourceBridges() );
//		$this->addData( 'sources', $this->logicBridge->getSources() );
		$this->addData( 'discovered', $this->logicBridge->discoverBridges() );
	}

	public function remove( $bridgeId ){
		$bridge	= $this->model->get( $bridgeId );
		if( !$bridge ){
			$this->messenger->noteError( 'Invalid bridge ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->model->remove( $bridgeId );
		$this->messenger->noteSuccess( 'Bridge "%s" removed.', $bridge->title );
		$this->restart( NULL, TRUE );
	}
}
?>
