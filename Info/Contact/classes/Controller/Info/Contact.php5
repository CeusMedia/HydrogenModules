<?php
class Controller_Info_Contact extends CMF_Hydrogen_Controller{
	public function index(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'index' );
		if( $request->has( 'save' ) ){
			$data	= $request->getAll();
			if( !trim( $request->get( 'name' ) ) )
				$messenger->noteError( $words->msgErrorNameMissing );
			if( !trim( $request->get( 'email' ) ) )
				$messenger->noteError( $words->msgErrorEmailMissing );
			if( !trim( $request->get( 'subject' ) ) )
				$messenger->noteError( $words->msgErrorSubjectMissing );
			if( !trim( $request->get( 'message' ) ) )
				$messenger->noteError( $words->msgErrorMessageMissing );
			if( !$messenger->gotError() ){
				$mail	= new Mail_Info_Contact( $this->env, $data );
die;
#				$mail->send();
				$messenger->noteSuccess( $words->msgSuccess );
				$this->restart( NULL, TRUE );
			}
		}
		$this->addData( 'name', $request->get( 'name' ) );
		$this->addData( 'email', $request->get( 'email' ) );
		$this->addData( 'subject', $request->get( 'subject' ) );
		$this->addData( 'message', $request->get( 'message' ) );
	}

}
?>
