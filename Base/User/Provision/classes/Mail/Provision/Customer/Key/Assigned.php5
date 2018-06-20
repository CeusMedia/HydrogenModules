<?php
class Mail_Provision_Customer_Key_Assigned extends Mail_Abstract{

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

		$this->setSubject( vsprintf( $words['mail-subjects']['keyAssigned'], array(
			$data['product']->title,
			$data['productLicense']->title,
		) ) );

		$this->setText( $text = $this->renderText( $mailData ) );
		$this->setHtml( $html = $this->renderHtml( $mailData ) );
		return (object) array( 'text' => $text, 'html' => $html );
	}

	protected function renderHtml( $data = array() ){
/*		$helperFacts	= new View_Helper_Mail_Facts( $this->env );
		$helperFacts->setFormat( View_Helper_Mail_Facts::FORMAT_HTML );*/
		return $this->view->loadContentFile( 'mail/provision/customer/key/assigned.html', $data );
	}

	protected function renderText( $data = array() ){
/*		$helperFacts	= new View_Helper_Mail_Facts( $this->env );
		$helperFacts->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT );*/
		return $this->view->loadContentFile( 'mail/provision/customer/key/assigned.txt', $data );
	}
}
?>
