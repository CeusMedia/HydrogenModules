<?php
class Mail_Work_Newsletter_Invite extends Mail_Abstract{

	protected function generate( $data = array() ){
		$words		= (object) $this->getWords( 'work/newsletter/reader', 'mail-invite' );
		$prefix	= $this->env->getConfig()->get( 'module.resource_mail.subject.prefix' );
		$subject	= ( $prefix ? $prefix.' ' : '' ) . $words->mailSubject;
		$this->mail->setSubject( $subject );

		$text		= $this->renderTextBody( $data );
		$html		= $this->renderHtmlBody( $data );
		$this->addHtmlBody( $html );
		$this->addTextBody( $text );
		return (object) array(
			'html'	=> $html,
			'text'	=> $text,
		);
	}

	public function renderHtmlBody( $data ){
		$baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUri();

		$groups	= array();
		foreach( $data['groups'] as $item )
			$groups[]	= UI_HTML_Tag::create( 'li', $item->title );

		$words				= $this->getWords( 'work/newsletter/reader' );
		$data['salutation']	= $words['salutations'][$data['reader']->gender];
		$data['key']		= substr( md5( 'InfoNewsletterSalt:'.$data['readerId'] ), 10, 10 );
		$data['baseUrl']	= $baseUrl;
		$data['groups']		= UI_HTML_Tag::create( 'ul', $groups );
		$data['emailHash']	= base64_encode( $data['reader']->email );

		return $this->view->loadContentFile( 'mail/work/newsletter/invite.html', $data );
	}

	public function renderTextBody( $data ){
		$baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUri();

		$groups	= array();
		foreach( $data['groups'] as $item )
			$groups[]	= '- '.$item->title;

		$words				= $this->getWords( 'work/newsletter/reader' );
		$data['salutation']	= $words['salutations'][$data['reader']->gender];
		$data['key']		= substr( md5( 'InfoNewsletterSalt:'.$data['readerId'] ), 10, 10 );
		$data['baseUrl']	= $baseUrl;
		$data['groups']		= join( "\n", $groups );
		$data['emailHash']	= base64_encode( $data['reader']->email );

		return $this->view->loadContentFile( 'mail/work/newsletter/invite.txt', $data );
	}
}
