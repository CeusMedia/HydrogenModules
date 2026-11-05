<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Info_Contact_Developer extends Controller{

	public function index(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'msg' );
		$options	= $this->env->getConfig()->getAll( 'module.info_contact_developer.', TRUE );
		if( !$options->get( 'receiver' ) )
			$messenger->noteFailure( 'No mail receiver defined in module configuration.' );
		else if( $request->has( 'save' ) ){
			if( !strlen( trim( $request->get( 'sender' ) ) ) )
				$messenger->noteError( $words->errorSenderMissing );
			else if( !strlen( trim( $request->get( 'subject' ) ) ) )
				$messenger->noteError( $words->errorSubjectMissing );
			else if( !strlen( trim( $request->get( 'message' ) ) ) )
				$messenger->noteError( $words->errorMessageMissing );
			else{
				$logic	= Logic_Mail::getInstance( $this->env );
				$data	= $request->getAll();
				$mail	= new Mail_Info_Contact_Developer( $this->env, $data );
				$receiver	= (object) ['email' => $options->get( 'receiver' )];
				$logic->handleMail( $mail, $receiver, $this->env->getLanguage()->getLanguage() );
				$messenger->noteSuccess( $words->success );
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
