<?php
class Mail_Contact_Developer extends Mail_Abstract{

	public function generate( $data = array() ){
		$config		= $this->env->getConfig();
		$appName	= $config->get( 'app.name' );

		$subject	= 'Report to Developer';
		$body	= '
<dl>
	<dt>Subject</dt>
	<dd>'.$data['subject'].'</dd>
	<dt>From</dt>
	<dd>'.$data['sender'].'</dd>
	<dt>Date</dt>
	<dd>'.date( "r").'</dd>
	<dt>Message</dt>
	<dd>
		<xmp class="code">
			'.htmlentities( $data['body'], ENT_COMPAT, 'UTF-8' ).'
		</xmp>
	</dd>
</dl>';

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
