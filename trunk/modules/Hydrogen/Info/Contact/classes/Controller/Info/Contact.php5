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

		$path	= "./info/contact";
		if( $this->env->getModules()->has( 'Info_Pages' ) ){
			$model	= new Model_Page( $this->env );
			$page	= $model->getByIndex( 'module', 'Info_Contact' );
			$path	= "./".$page->identifier;
		}
		
		$this->addData( 'formPath', $path );
		$this->addData( 'name', $request->get( 'name' ) );
		$this->addData( 'email', $request->get( 'email' ) );
		$this->addData( 'subject', $request->get( 'subject' ) );
		$this->addData( 'message', $request->get( 'message' ) );
	}

}
?>
