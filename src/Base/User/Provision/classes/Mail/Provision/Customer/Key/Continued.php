<?php
class Mail_Provision_Customer_Key_Continued extends Mail_Abstract
{
	/**
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
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

		$this->setSubject( vsprintf( $words['mail-subjects']['keyContinued'], [
			$data['product']->title,
			$data['productLicense']->title,
		] ) );
		$this->setText( $this->renderText( $mailData ) );
		$this->setHtml( $this->renderHtml( $mailData ) );
		return $this;
	}

	/**
	 *	@param		array		$data
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function renderHtml( array $data = [] ): string
	{
		return $this->view->loadContentFile( 'mail/provision/customer/key/continued.html', $data );
	}

	/**
	 *	@param		array		$data
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function renderText( array $data = [] ): string
	{
		return $this->view->loadContentFile( 'mail/provision/customer/key/continued.txt', $data );
	}
}
