<?php
class Controller_Info_Testimonial extends CMF_Hydrogen_Controller{

	protected $request;
	protected $messenger;
	protected $model;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.info_testimonials.', TRUE );
		$this->model		= new Model_Testimonial( $this->env );

		$indices	= array( 'status' => 1 );
		$orders		= array( 'rank' => 'ASC', 'timestamp' => 'DESC' );
		$entries	= $this->model->getAll( $indices, $orders );
		$this->addData( 'testimonials', $entries );
	}

	public function addComment(){
		if( $this->request->get( 'save' ) ){
			$language	= $this->env->getLanguage();
			$data		= $this->request->getAll();
			$data['timestamp']	= time();
			$testimonialId	= $this->model->add( $data );

			$logic		= new Logic_Mail( $this->env );												//  get mailer logic
			$data		= array( 'entry' => $this->model->get( $testimonialId ) );					//  prepare mail data
			$mail		= new Mail_Info_Testimonial_New( $this->env, $data );						//  generate mail to post author
			$receiver	= (object) array( 'email' => $this->moduleConfig->get( 'mail.receiver' ) );	//	get mail receiver from module config
			$logic->handleMail( $mail, $receiver, $language->getLanguage() );						//  send or enqueue mail

			$this->messenger->noteSuccess( 'Der Kommentar wurde gespeichert.<br/>Er wird angezeigt, nachdem er geprüft und frei geschaltet wurde.' );
		}
		$this->restart( './info/testimonial' );
	}

	public function index(){}
}
?>
