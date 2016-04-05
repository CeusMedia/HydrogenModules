<?php
class Mail_Info_Contact_Developer extends Mail_Abstract{

	public function generate( $data = array() ){
		$config			= $this->env->getConfig();
		$appName		= $config->get( 'app.name' );
		$data['date']		= date( "r" );
		$data['message']	= htmlentities( $data['message'], ENT_COMPAT, 'UTF-8' );

		$subject	= 'Report to Developer';
		$body		= $this->view->loadContentFile( 'mail/info/contact/developer.html', $data );

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
