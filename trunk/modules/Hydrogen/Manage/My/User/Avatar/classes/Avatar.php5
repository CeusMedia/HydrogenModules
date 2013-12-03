<?php
class View_Helper_Avatar{

	public function __construct(){
		$this->env	= $env;
	}

	public function getImage( $userId, $options = array() ){
		$model	= new Model_User_Avatar( $this->env );
		$user	= $model->getByIndex( 'userId', $userId );
		if( !$user )
			return '';
		
	}
}
?>
