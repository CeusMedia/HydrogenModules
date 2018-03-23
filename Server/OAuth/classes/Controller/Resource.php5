<?php
class Controller_Resource extends CMF_Hydrogen_Controller {

	public function __construct( $env, $setupView = TRUE ){
		parent::__construct( $env, FALSE );
	}

	protected function checkToken(){
		$request	= $this->env->getRequest();
		$token		= $request->get( 'access_token' );
		if( !$token ){
			$header	= $request->getHeadersByName( 'Authorization', TRUE );
			if( !$header )
				$this->handleJsonErrorResponse( array(
					'message'		=> 'missing_authorization',
					'description'	=> 'Request to resource needs authorization header or access token in request data.'
				) );
			if( !preg_match( "/^(token|bearer) (.+)$/i", $header->getValue() ) )
				$this->handleJsonErrorResponse( array(
					'message'		=> 'missing_authorization_type',
					'description'	=> 'Used authorization header type is not supported. Use "Bearer" or "Token" instead.',
				) );
			$token	= preg_replace( "/^(token|bearer) (.+)$/i", "\\2", $header->getValue() );
			$request->set( 'access_token', $token );
		}
		$modelToken		= new Model_Oauth_AccessToken( $this->env );
		if( !$modelToken->getByIndex( 'token', $token ) )
			$this->handleJsonErrorResponse( array(
				'message'		=> 'invalid_access_token',
				'description'	=> 'Access token provided by request is invalid or outdated.',
			) );
	}

	protected function checkPostRequest(){
		$request	= $this->env->getRequest();
		if( strtoupper( $request->getMethod() ) === "POST" )
			return TRUE;
		$this->handleJsonErrorResponse( array(
			'message'		=> 'invalid_request_method',
			'description'	=> 'Use POST HTTP request method for this resource.'
		) );
	}

	protected function getPostData(){
		$data	= array();
		$body	= $this->env->getRequest()->getBody();
		if( strlen( trim( $body ) ) ){
			if( function_exists( 'mb_parse_str' ) )
				mb_parse_str( $body, $data );
			else
				parse_str( $body, $data );
		}
		return $data;
	}

	protected function isWriteAction(){
		return strlen( trim( $this->env->getRequest()->getBody() ) ) > 0;
	}

	protected function hasWriteAccess( $userId, $resource ){
		return TRUE;
	}

	public function me(){
		$this->checkToken();
		$this->checkPostRequest();
		$request	= $this->env->getRequest();
		$modelToken	= new Model_Oauth_AccessToken( $this->env );
		$modelUser	= new Model_User( $this->env );
		$token		= $modelToken->getByIndex( 'token', $request->get( 'access_token' ) );
		if( $token->userId < 1 )
			$this->handleJsonResponse( 'error', array(
				'message'		=> 'not_an_user_token',
				'description'	=> '...',
			), 404 );
		$user		= $modelUser->get( $token->userId );
		if( !$user )
			$this->handleJsonResponse( 'error', array(
				'message'		=> 'invalid_user',
				'description'	=> '...',
			), 404 );
		$this->handleJsonResponse( 'data', array(
			'user'	=> $user,
		) );
	}

	public function user( $username ){
		$this->checkToken();
//		$this->checkPostRequest();
		$request	= $this->env->getRequest();
		$model		= new Model_User( $this->env );
		$user		= $model->getByIndex( 'username', $username );
		if( !$user ){
			$this->handleJsonResponse( 'error', array(
				'message'		=> 'invalid_username',
				'description'	=> '...',
			), 404 );
		}
		if( $this->isWriteAction() ){
			$data = $this->getPostData();
			if( isset( $data['password'] ) )
				unset( $data['password'] );
			$model->edit( $user->userId, $data );
			$user	= $model->getByIndex( 'username', $username );
		}
		unset( $user->password );
		$this->handleJsonResponse( 'data', array(
			'user'	=> $user,
		) );
	}

	public function userId( $userId ){
		$this->checkToken();
		$this->checkPostRequest();
		$request	= $this->env->getRequest();
		$model		= new Model_User( $this->env );
		$user		= $model->get( $userId );
		if( !$user ){
			$this->handleJsonResponse( 'error', array(
				'message'		=> 'invalid_user_id',
				'description'	=> '...',
			), 404 );
		}
		if( $this->isWriteAction() ){
			$data = $this->getPostData();
			if( isset( $data['password'] ) )
				unset( $data['password'] );
			$model->edit( $user->userId, $data );
			$user		= $model->get( $userId );
		}
		unset( $user->password );
		$this->handleJsonResponse( 'data', array(
			'user'	=> $user,
		) );
	}
}
