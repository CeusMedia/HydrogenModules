<?php
class Mail_Provision_Customer_License_Replaced extends Mail_Abstract
{
	protected function generate(): self
	{
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$words		= $this->env->getLanguage()->getWords( 'user/provision' );
		$data		= $this->data;

		$mailData	= array_merge( $data, [
			'appBaseUrl'	=> $this->env->url,
			'appTitle'		=> $wordsMain['main']['title'],
			'words'			=> $words,
		] );
		$mailData['userLicense']->date		= date( "d.m.Y", $data['userLicense']->createdAt );
		$mailData['userLicense']->duration	= $words['durations'][$data['userLicense']->duration];
//		$mailData['config']		= $this->env->getConfig()->getAll();

		$this->setSubject( vsprintf( $words['mail-subjects']['licenseReplaced'], [
			$data['product']->title,
			$data['productLicense']->title,
		] ) );
		$this->setText( $this->renderText( $mailData ) );
		$this->setHtml( $this->renderHtml( $mailData ) );
		return $this;
	}

	protected function renderHtml( array $data = [] ): string
	{
		return $this->view->loadContentFile( 'mail/provision/customer/license/replaced.html', $data );
	}

	protected function renderText( array $data = [] ): string
	{
		return $this->view->loadContentFile( 'mail/provision/customer/license/replaced.txt', $data );
	}
}
