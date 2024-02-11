<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Admin_Mail_Template extends AjaxController
{
	protected Model_Mail_Template $modelTemplate;

	/**
	 *	@param		string		$templateId
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 *	@throws		\CeusMedia\Common\Exception\Data\Ambiguous
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function render( string $templateId ): void
	{
		$this->checkTemplate( $templateId );
		$mail		= new Mail_Test( $this->env, ['mailTemplateId' => $templateId] );
		$helper		= new View_Helper_Mail_View_HTML( $this->env );
		$helper->setMailObjectInstance( $mail );
		$this->respondData( ['html' => $helper->render()] );
	}

	/**
	 *	@param		string		$templateId
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function saveCss( string $templateId ): void
	{
		$content	= $this->env->getRequest()->get( 'content' );
		$this->modelTemplate->edit( $templateId, [
			'css'			=> trim( $content ),
			'modifiedAt'	=> time(),
		], FALSE );
		$this->respondData( TRUE );
	}

	/**
	 *	@param		string		$templateId
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function saveHtml( string $templateId ): void
	{
		$content	= $this->env->getRequest()->get( 'content' );
		$this->modelTemplate->edit( $templateId, [
			'html'			=> trim( $content ),
			'modifiedAt'	=> time(),
		], FALSE );
		$this->respondData( TRUE );
	}

	/**
	 *	@param		string		$templateId
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function savePlain( string $templateId ): void
	{
		$content	= $this->env->getRequest()->get( 'content' );
		$this->modelTemplate->edit( $templateId, [
			'plain'			=> trim( $content ),
			'modifiedAt'	=> time(),
		], FALSE );
		$this->respondData( TRUE );
	}

	public function setTab( $tabId ): void
	{
		if( strlen( trim( $tabId ) ) && $tabId != 'undefined' )
			$this->env->getSession()->set( 'admin-mail-template-edit-tab', $tabId );
		$this->respondData( TRUE );
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->modelTemplate	= new Model_Mail_Template( $this->env );
	}

	/**
	 *	@param		string		$templateId
	 *	@param		bool		$strict
	 *	@return		object|FALSE
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkTemplate( string $templateId, bool $strict = TRUE ): object|FALSE
	{
		$template	= $this->modelTemplate->get( $templateId );
		if( $template )
			return $template;
		if( $strict )
			throw new RangeException( 'Invalid template ID' );
		return FALSE;
	}
}
