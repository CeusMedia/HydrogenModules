<?php

use CeusMedia\Common\Renderable;
use CeusMedia\HydrogenFramework\Environment\Web as Environment;
use CeusMedia\HydrogenFramework\View;

class View_Helper_Auth_Local_Panel_Login implements Renderable
{
	protected Environment $env;
	protected bool $useOAuth2	= TRUE;
	protected bool $useRemember	= TRUE;
	protected bool $useRegister	= TRUE;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function render(): string
	{
		$words		= $this->env->getLanguage()->getWords( 'auth/local' );
		$wordsLogin	= $words['login'];
		$request	= $this->env->getRequest();
		$view		= new View( $this->env );
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

	public function setUseOAuth2( bool $use = TRUE ): self
	{
		$this->useOAuth2 = $use;
		return $this;
	}

	public function setUseRemember( bool $use = TRUE ): self
	{
		$this->useRemember = $use;
		return $this;
	}

	public function setUseRegister( bool $use = TRUE ): self
	{
		$this->useRegister = $use;
		return $this;
	}
}
