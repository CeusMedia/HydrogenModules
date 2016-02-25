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
		if( $this->request->has( 'save' ) ){
			foreach( $this->model->getColumns() as $key )
				$data[$key]	= $this->request->get( $key );
			$data['timestamp']	= time();
			$testimonialId	= $this->model->add( $data );
			$this->messenger->noteSuccess( "Der Kommentar wurde hinzugefügt." );
			$this->restart( NULL, TRUE );
		}
		$comment	= array();
		foreach( $this->model->getColumns() as $key )
			$testimonial[$key]	= $this->request->get( $key );
		$testimonial['courseId']	= $this->session->get( 'filter_manage_course_courseId' );
		$this->addData( 'testimonial', (object) $testimonial );
	}

	public function edit( $testimonialId ){
//		if( !( strlen( trim( $locationId ) ) && (int) $locationId ) )
//			throw new OutOfRangeException( 'No location ID given' );
		$testimonial		= $this->model->get( (int) $testimonialId );
		if( !$testimonial )
			throw new OutOfRangeException( 'Invalid testimonial ID given' );

		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$this->model->edit( $testimonialId, $data, FALSE );
			$this->messenger->noteSuccess( "Der Kommentar wurde gespeichert." );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'testimonial', $testimonial );
	}

	public function index(){
	}

	public function remove( $testimonialId ){
		$testimonial	= $this->model->get( $testimonialId );
		if( !$testimonial )
			throw new OutOfRangeException( 'Invalid testimonial ID given' );
		$this->model->remove( $testimonialId );
		$this->restart( NULL, TRUE );
	}
}
?>
