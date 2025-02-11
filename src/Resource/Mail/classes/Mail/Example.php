<?php

use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\HydrogenFramework\View\Helper\Content as ContentHelper;

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
	protected function generate(): static
	{
		$wordsModule	= (object) $this->getWords( 'myModule', 'myMailSection' );					//  @todo change this!
		$this->setSubject( $wordsModule->subject );

		$configModule	= $this->env->getConfig()->getAll( 'module.myModule.', TRUE );				//  @todo change this!
		$templateId		= (int) $configModule->get( 'mailTemplateId' );

		$this->setHtml( $this->renderBodyHtml(), $templateId );
		$this->setText( $this->renderBodyText(), $templateId );

		return $this;
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function renderBodyHtml(): string
	{
		$helper	= new ContentHelper( $this->env );
		if( $helper->has( 'mails/myModule/myAction.html' ) )
			return $helper->setFileKey( 'mails/myModule/myAction.html' )
				->setData( $this->data )
				->render();
		return '
<div id="layout-mail">
	<div id="layout-content">
		This is an example mail.
	</div>
</div>';
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function renderBodyText(): string
	{
		$helper	= new ContentHelper( $this->env );
		if( $helper->has( 'mails/myModule/myAction.txt' ) )
			return $helper->setFileKey( 'mails/myModule/myAction.txt' )
				->setData( $this->data )
				->render();
		return 'This is an example mail.';
	}
}
