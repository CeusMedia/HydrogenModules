<?php
class Controller_Manage_Form extends CMF_Hydrogen_Controller{

	protected $modelForm;
	protected $modelFill;

	protected function __onInit(){
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelFill	= new Model_Form_Fill( $this->env );
	}

	protected function checkId( $formId ){
		if( !$formId )
			throw new RuntimeException( 'No form ID given' );
		if( !( $form = $this->modelForm->get( $formId ) ) )
			throw new DomainException( 'Invalid form ID given' );
		return $form;
	}

	protected function checkIsPost(){
		if( !$this->env->getRequest()->isMethod( 'POST' ) )
			throw new RuntimeException( 'Access denied: POST requests, only' );
	}

	public function add(){
		$this->checkIsPost();
		$data	= $this->env->getRequest()->getAll();
		$data['timestamp']	= time();
		$formId	= $this->modelForm->add( $data, FALSE );
		$this->restart( '?action=form_edit&id='.$formId );
	}

	public function confirm(){
		$fillId		= $this->env->getRequest()->get( 'fillId' );
		$fill		= $this->modelFill->get( $fillId );
		$this->modelFill->edit( $fillId, array(
			'status'		=> Model_Fill::STATUS_CONFIRMED,
			'modifiedAt'	=> time(),
		) );
		return 'Okay.';
	}

	public function edit( $formId ){
		$this->checkIsPost();
		$this->checkId( $formId );
		$data	= $this->env->getRequest()->getAll();
		$data['timestamp']	= time();
		$this->modelForm->edit( $formId, $data, FALSE );
		$this->restart( 'edit/'.$formId, TRUE );
	}

	public function fill( $formId ){
		$this->checkId( $formId );
		$request	= $this->env->getRequest();
		$form		= $this->modelForm->get( $formId );
		$data		= array(
			'formId'		=> $formId,
			'status'		=> $form->type ? Model_Fill::STATUS_CONFIRMED : Model_Fill::STATUS_NEW,
			'createdAt'		=> time(),
			'email'			=> $request->get( 'email' ),
		);
		$fillId		= $this->modelFill->add( $data );
		$transport	= $this->getTransport();
		if( $form->type == Model_Form::TYPE_NORMAL ){
//			$this->sendFillToSender( $fillId );
			$this->sendFillToReceivers( $fillId );
			return 'Danke!';
		}
		else if( $form->type == Model_Form::TYPE_CONFIRM ){
//			$this->sendFillToSender( $fillId );
			$this->sendConfirmMail( $fillId );
			return 'Danke! Sie müssen aber noch bestätigen. Siehe E-Mail...';
		}
	}

	public function remove( $formId ){
		$this->checkId( $formId );
		$this->modelForm->remove( $formId );
		$this->restart( NULL, TRUE );
	}

	protected function sendConfirmMail( $fillId ){
		$fill		= $this->modelFill->get( $fillId );
		$form		= $this->modelForm->get( $fill->formId );
		$transport	= $this->getTransport();
		$mail		= new \CeusMedia\Mail\Message();
		$mail->addRecipient( $fill->email );
		$transport->send( $mail );
	}
}

