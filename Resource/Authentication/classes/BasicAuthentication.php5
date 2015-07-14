<?php
class BasicAuthentication{

	public function __construct( $env, $realm ){
		$this->env		= $env;
		$this->realm	= $realm;
	}

	public function authenticate(){
		$server		= new ADT_List_Dictionary( $_SERVER );
		$username	= trim( $server->get( 'PHP_AUTH_USER' ) );
		$password	= trim( $server->get( 'PHP_AUTH_PW' ) );
		if( strlen( $username ) ){
			$model		= new Model_User( $this->env );
			$user		= $model->getByIndex( 'username', $username );
			if( $user && $user->password == md5( $password ) )
				return $user->userId;
		}
		header( 'WWW-Authenticate: Basic realm="'.addslashes( $this->realm ).'"' ); 
		header( 'HTTP/1.0 401 Unauthorized' ); 
//		header( '401 Forbidden' );
		print( '<h1>Authentication failed.</h1>' );
		exit;
	}
}
?>
