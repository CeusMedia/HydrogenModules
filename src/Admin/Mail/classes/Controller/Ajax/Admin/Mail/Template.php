<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Admin_Mail_Template extends AjaxController
{
	protected Model_Mail_Template $modelTemplate;

	public function render( $templateId ): void
	{
		$this->checkTemplate( $templateId );
		$mail		= new Mail_Test( $this->env, ['mailTemplateId' => $templateId] );
		$helper		= new View_Helper_Mail_View_HTML( $this->env );
		$helper->setMailObjectInstance( $mail );
		$this->respondData( ['html' => $helper->render()] );
	}

	public function saveCss( $templateId ): void
	{
		$content	= $this->env->getRequest()->get( 'content' );
		$this->modelTemplate->edit( $templateId, [
			'css'			=> trim( $content ),
			'modifiedAt'	=> time(),
		], FALSE );
		$this->respondData( TRUE );
	}

	public function saveHtml( $templateId ): void
	{
		$content	= $this->env->getRequest()->get( 'content' );
		$this->modelTemplate->edit( $templateId, [
			'html'			=> trim( $content ),
			'modifiedAt'	=> time(),
		], FALSE );
		$this->respondData( TRUE );
	}

	public function savePlain( $templateId ): void
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
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->modelTemplate	= new Model_Mail_Template( $this->env );
	}

	/**
	 *	@param		string		$templateId
	 *	@param		bool		$strict
	 *	@return		object|FALSE
	 */
	protected function checkTemplate( string $templateId, bool $strict = TRUE )
	{
		$template	= $this->modelTemplate->get( $templateId );
		if( $template )
			return $template;
		if( $strict )
			throw new RangeException( 'Invalid template ID' );
		return FALSE;
	}
}
