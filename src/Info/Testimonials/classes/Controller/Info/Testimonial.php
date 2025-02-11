<?php

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Info_Testimonial extends Controller
{
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Model_Testimonial $model;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addComment(): void
	{
		if( $this->request->get( 'save' ) ){
			$language	= $this->env->getLanguage();
			$data		= $this->request->getAll();
			$data['timestamp']	= time();
			$testimonialId	= $this->model->add( $data );

			/** @var Logic_Mail $logic */
			$logic		= Logic_Mail::getInstance( $this->env );									//  get mailer logic
			$data		= ['entry' => $this->model->get( $testimonialId )];					//  prepare mail data
			$mail		= new Mail_Info_Testimonial_New( $this->env, $data );						//  generate mail to post author
			$receiver	= (object) ['email' => $this->moduleConfig->get( 'mail.receiver' )];	//	get mail receiver from module config
			$logic->handleMail( $mail, $receiver, $language->getLanguage() );						//  send or enqueue mail

			$this->messenger->noteSuccess( 'Der Kommentar wurde gespeichert.<br/>Er wird angezeigt, nachdem er geprüft und frei geschaltet wurde.' );
		}
		$this->restart( './info/testimonial' );
	}

	public function index()
	{
	}

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.info_testimonials.', TRUE );
		$this->model		= new Model_Testimonial( $this->env );

		$indices	= ['status' => 1];
		$orders		= ['rank' => 'ASC', 'timestamp' => 'DESC'];
		$entries	= $this->model->getAll( $indices, $orders );
		$this->addData( 'testimonials', $entries );
	}
}
