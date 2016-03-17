<?php
class Controller_Manage_Testimonial extends CMF_Hydrogen_Controller{

	protected $model;
	protected $messenger;
	protected $request;
	protected $session;

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_Testimonial( $this->env );
		$testimonials		= $this->model->getAll( array(), array( 'rank' => 'ASC' ) );
		$this->addData( 'testimonials', $testimonials );
	}

	public function add(){
		$words	= (object) $this->getWords( 'msg' );
		if( $this->request->has( 'save' ) ){
			foreach( $this->model->getColumns() as $key )
				$data[$key]	= $this->request->get( $key );
			$data['timestamp']	= time();
			$testimonialId	= $this->model->add( $data );
			$this->messenger->noteSuccess( $words->successAdded );
			$this->restart( NULL, TRUE );
		}
		$comment	= array();
		foreach( $this->model->getColumns() as $key )
			$testimonial[$key]	= $this->request->get( $key );
		$testimonial['courseId']	= $this->session->get( 'filter_manage_course_courseId' );
		$this->addData( 'testimonial', (object) $testimonial );
	}

	public function edit( $testimonialId ){
		$words			= (object) $this->getWords( 'msg' );
//		if( !( strlen( trim( $locationId ) ) && (int) $locationId ) )
//			throw new OutOfRangeException( 'No location ID given' );
		$testimonial	= $this->model->get( (int) $testimonialId );
		if( !$testimonial )
			$this->restart( NULL, TRUE );
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$this->model->edit( $testimonialId, $data, FALSE );
			$this->messenger->noteSuccess( $words->successSaved );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'testimonial', $testimonial );
	}

	public function index(){
	}

	public function remove( $testimonialId ){
		$words			= (object) $this->getWords( 'msg' );
		$testimonial	= $this->model->get( $testimonialId );
		if( !$testimonial )
			$this->restart( NULL, TRUE );
		$this->messenger->noteSuccess( $words->successRemoved );
		$this->model->remove( $testimonialId );
		$this->restart( NULL, TRUE );
	}
}
?>
