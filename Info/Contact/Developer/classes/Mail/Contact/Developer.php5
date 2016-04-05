<?php
class Mail_Contact_Developer extends Mail_Abstract{

	public function generate( $data = array() ){
		$config			= $this->env->getConfig();
		$appName		= $config->get( 'app.name' );

		$subject		= 'Report to Developer';
		$data['date']	= date( "r" );
		$body			= $this->view->loadContentFile( 'mail/info/contact/developer.html', $data );

		$this->page->addBody( '<h3><span class="muted">'.$appName.'</span> Report to Developer</h3>' );
		$this->page->addBody( $body );
		if( $this->env->getModules()->has( 'UI_Bootstrap' ) )
			$this->addThemeStyle( 'bootstrap.min.css' );
		else
			$this->addThemeStyle( 'mail.min.css' );

		$this->setSubject( $subject );
		$this->addHtmlBody( $this->page->build() );
	}
}
?>
