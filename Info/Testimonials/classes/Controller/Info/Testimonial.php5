<?php
class Controller_Info_Testimonial extends CMF_Hydrogen_Controller{

	protected $request;
	protected $messenger;
	protected $model;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_Testimonial( $this->env );

		$indices	= array( 'status' => 1 );
		$orders		= array( 'rank' => 'ASC', 'timestamp' => 'DESC' );
		$entries	= $this->model->getAll( $indices, $orders );
		$this->addData( 'testimonials', $entries );
	}

	public function addComment(){
		if( $this->request->get( 'save' ) ){
			$data	= $this->request->getAll();
			$data['timestamp']	= time();
			$this->model->add( $data );
			$this->messenger->noteSuccess( 'Der Kommentar wurde gespeichert.<br/>Er wird angezeigt, nachdem er geprüft und frei geschaltet wurde.' );
		}
		$this->restart( './info/testimonial' );
	}

	public function index(){}
}
?>
