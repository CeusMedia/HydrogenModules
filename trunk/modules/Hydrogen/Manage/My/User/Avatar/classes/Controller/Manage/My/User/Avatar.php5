<?php
class Controller_Manage_My_User_Avatar extends CMF_Hydrogen_Controller{

	protected $userId;

	protected function __onInit(){
		$this->userId	= $this->env->getSession()->get( 'userId' );
	}

	public function index(){
		$model		= new Model_User_Avatar( $this->env );
		$this->addData( 'avatar', $model->getByIndex( 'userId', $this->userId ) );					//  
	}

	public function remove(){
		$model		= new Model_User_Avatar( $this->env );
		$model->removeByIndex( 'userId', $this->userId );
		$this->restart( NULL, TRUE );																//  @todo: make another redirect possible
	}

	public function upload(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'update' );
		$model		= new Model_User_Setting( $this->env );

print_m( $request->getAll() );
die;

		if( $count )
			$messenger->noteSuccess( $words->msgSuccess );
		if( $request->get( 'from' ) )
			$this->restart( $request->get( 'from' ) );
		$this->restart( NULL, TRUE );																//  @todo: make another redirect possible
	}
}
?>
