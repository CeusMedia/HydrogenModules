<?php
class Mail_Provision_Customer_License_Revoked extends Mail_Abstract{

	protected function generate( $data = array() ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$words		= $this->env->getLanguage()->getWords( 'user/provision' );

		$mailData	= array_merge( $data, array(
			'appBaseUrl'	=> $this->env->url,
			'appTitle'		=> $wordsMain['main']['title'],
			'words'			=> $words,
		) );
		$mailData['userLicense']->date		= date( "d.m.Y", $data['userLicense']->createdAt );
		$mailData['userLicense']->duration	= $words['durations'][$data['userLicense']->duration];
//		$mailData['config']		= $this->env->getConfig()->getAll();

		$this->setSubject( vsprintf( $words['mail-subjects']['licenseRevoked'], array(
			$data['product']->title,
			$data['productLicense']->title,
		) ) );
		$this->setText( $text = $this->renderText( $mailData ) );
		$this->setHtml( $html = $this->renderHtml( $mailData ) );
		return (object) array( 'text' => $text, 'html' => $html );
	}

	protected function renderHtml( $data = array() ){
		return $this->view->loadContentFile( 'mail/provision/customer/license/revoked.html', $data );
	}

	protected function renderText( $data = array() ){
		return $this->view->loadContentFile( 'mail/provision/customer/license/revoked.txt', $data );
	}
}
?>
