<?php

use CeusMedia\Common\Exception\IO as IoException;

class Mail_Example extends Mail_Abstract
{
	/**
	 *	Create mail body and sets subject and body on mail object.
	 *	By using methods setText and setHtml to assign generated contents,
	 *	a detected mail template will be applied,
	 *	the mail object will receive the rendered contents as new mail parts and
	 *	generated and rendered contents will be stored in mail class as contents.
	 *	@access		protected
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		IoException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): self
	{
		$wordsMain		= $this->getWords( 'main' );												//  main words of application
		$wordsModule	= (object) $this->getWords( 'myModule', 'myMailSection' );					//  @todo change this!
		$this->setSubject( $wordsModule->subject );

		$configModule	= $this->env->getConfig()->getAll( 'module.myModule.', TRUE );				//  @todo change this!
		$templateId		= (int) $configModule->get( 'mailTemplateId' );

		$this->setHtml( $this->renderBodyHtml( $wordsModule ), $templateId );
		$this->setText( $this->renderBodyText( $wordsModule ), $templateId );

		return $this;
	}

	protected function renderBodyHtml( $wordsModule ): string
	{
		if( $this->view->hasContentFile( 'mails/myModule/myAction.html' ) )
			$body	= $this->view->loadContentFile( 'mails/myModule/myAction.html', $this->data );
		else
			$body	= '
<div id="layout-mail">
	<div id="layout-content">
		This is an example mail.
	</div>
</div>';
		return $body;
	}

	protected function renderBodyText( $wordsModule ): string
	{
		if( $this->view->hasContentFile( 'mails/myModule/myAction.txt' ) )
			$body	= $this->view->loadContentFile( 'mails/myModule/myAction.txt', $this->data );
		else
			$body	= 'This is an example mail.';
		return $body;
	}
}
