<?php
class Mail_Example extends Mail_Abstract{

	protected function generate( $data = array() ){
		$words		= (object) $this->getWords( 'myModule', 'myMailSection' );						//  @todo change this!

		$prefix		= $this->env->getConfig()->get( 'module.resource_mail.subject.prefix' );
		$this->mail->setSubject( ( $prefix ? $prefix.' ' : '' ) . $w->subject );

		$html		= $this->renderBody( $data );
		$html		= chunk_split( base64_encode( $html ), 78 );
		$mailBody	= new Net_Mail_Body( $html, Net_Mail_Body::TYPE_HTML );
		$mailBody->setContentEncoding( 'base64' );
		$this->mail->addBody( $mailBody );

		return $html;
	}

	public function renderBody( $data ){
		$words		= (object) $this->getWords( 'myModule', 'myMailSection' );						//  @todo change this!
		$body		= '
<div id="layout-mail">
	<div id="layout-content">
		This is an example mail.
	</div>
</div>';

		if( $this->env->getConfig()->get( 'layout.primer' ) )
			$this->addPrimerStyle( 'layout.css' );
		if( $this->env->getModules()->has( 'UI_Bootstrap' ) )
			$this->addThemeStyle( 'bootstrap.min.css' );
		$this->addThemeStyle( 'layout.css' );
		$this->addThemeStyle( 'myModule.css' );														//  @todo adjust and enable or remove this!

		$this->page->setBaseHref( $this->env->url );
		$this->page->addBody( $body );
		$bodyClass	= 'moduleMyModule';																	//  @todo change this!
		return $this->page->build( array( 'class' => $bodyClass ) );
	}
}
?>