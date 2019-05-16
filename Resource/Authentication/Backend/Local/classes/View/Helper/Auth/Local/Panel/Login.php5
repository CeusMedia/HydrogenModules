<?php
class View_Helper_Auth_Local_Panel_Login implements Renderable{

	protected $env;
	protected $useOAuth2	= TRUE;
	protected $useRemember	= TRUE;
	protected $useRegister	= TRUE;

	public function __construct( CMF_Hydrogen_Environment $env ){
		$this->env	= $env;
	}

	public function render(){
		$words	= $this->env->getLanguage()->getWords( 'auth/local' );
		$wordsLogin	= $words['login'];
		$request	= $this->env->getRequest();
		$view	= new CMF_Hydrogen_View( $this->env );
		$view->addData( 'words', $words );
		$view->addData( 'useOauth2', $this->useOAuth2 );
		$view->addData( 'useRemember', $this->useRemember );
		$view->addData( 'useRegister', $this->useRegister );
		$view->addData( 'login_remember', $request->get( 'login_remember' ) );
		$view->addData( 'login_username', $request->get( 'login_username' ) );
		$view->addData( 'from', $request->get( 'from' ) );
		$view->addData( 'useCsrf', class_exists( 'View_Helper_CSRF' ) );
		return $view->loadTemplateFile( 'auth/local/panel/login.php' );
	}

	public function setUseOAuth2( $use ){
		$this->useOAuth2 = $use;
	}

	public function setUseRemember( $use ){
		$this->useRemember = $use;
	}

	public function setUseRegister( $use ){
		$this->useRegister = $use;
	}
}

