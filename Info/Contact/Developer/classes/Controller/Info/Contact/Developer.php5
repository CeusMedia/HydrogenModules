<?php
class Controller_Info_Contact_Developer extends CMF_Hydrogen_Controller{

	public function index(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$options	= $this->env->getConfig()->getAll( 'module.info_contact_developer.', TRUE );
		if( !$options->get( 'receiver' ) )
			$messenger->noteFailure( 'No mail receiver defined in module configuration.' );
		else if( $request->has( 'save' ) ){
			if( !strlen( trim( $request->get( 'sender' ) ) ) )
				$messenger->noteError( 'Sender is missing.' );
			else if( !strlen( trim( $request->get( 'subject' ) ) ) )
				$messenger->noteError( 'Subject is missing.' );
			else if( !strlen( trim( $request->get( 'message' ) ) ) )
				$messenger->noteError( 'Message is missing.' );
			else{
				$logic	= new Logic_Mail( $this->env );
				$data	= $request->getAll();
				$mail	= new Mail_Info_Contact_Developer( $this->env, $data );
				$receiver	= (object) array( 'email' => $options->get( 'receiver' ) );
				$logic->handleMail( $mail, $receiver, $this->env->getLanguage()->getLanguage() );
				$messenger->noteSuccess( "Danke!" );
				$request->set( 'sender', '' );
				$request->set( 'subject', '' );
				$request->set( 'message', '' );
			}
		}

		$this->addData( 'sender', $request->get( 'sender' ) );
		$this->addData( 'subject', $request->get( 'subject' ) );
		$this->addData( 'message', $request->get( 'message' ) );
	}
}
