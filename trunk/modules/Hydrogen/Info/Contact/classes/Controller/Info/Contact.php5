<?php
class Controller_Info_Contact extends CMF_Hydrogen_Controller{
	public function index(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'index' );
		if( $request->has( 'save' ) ){
			if( !trim( $request->get( 'name' ) ) )
				$messenger->noteError( $words->msgErrorNameMissing );
			if( !trim( $request->get( 'email' ) ) )
				$messenger->noteError( $words->msgErrorEmailMissing );
			if( !trim( $request->get( 'subject' ) ) )
				$messenger->noteError( $words->msgErrorSubjectMissing );
			if( !trim( $request->get( 'message' ) ) )
				$messenger->noteError( $words->msgErrorMessageMissing );
			if( !$messenger->gotError() ){
				$data	= $request->getAll();
				try{
					$mail	= new Mail_Info_Contact( $this->env, $data );
					$messenger->noteSuccess( $words->msgSuccess );
					$this->restart( NULL, TRUE );
				}
				catch( Exception $e ){
					die( $e->getMessage() );
				}
			}
		}
		$this->addData( 'name', $request->get( 'name' ) );
		$this->addData( 'email', $request->get( 'email' ) );
		$this->addData( 'subject', $request->get( 'subject' ) );
		$this->addData( 'message', $request->get( 'message' ) );
	}

}
?>
