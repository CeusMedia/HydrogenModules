<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_Form extends View
{
	/**
	 *	Will automatically load template manage/form/add and return to app flow.
	 *	@return		void
	 */
	public function add(): void
	{
	}

	/**
	 *	Will automatically load template manage/form/edit and return to app flow.
	 *	@return		void
	 */
	public function edit(): void
	{
	}

	/**
	 *	Will automatically load template manage/form/index and return to app flow.
	 *	@return		void
	 */
	public function index(): void
	{
	}

	/**
	 *	This view is meant to delivery a final view of the form for requests from OUTSIDE.
	 *	Uses form view helper to render form content.
	 *	Stops app flow by directly printing the form content with exit.
	 *	@return		never
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view(): never
	{
		$formId	= $this->getData( 'formId' );
		$mode	= $this->getData( 'mode', '' );

		$helper = new View_Helper_Form( $this->env );
		if( $mode )
			$helper->setMode( $mode );
		print $helper->setId( $formId )->render();
		exit;
	}

	/**
	 *	Loads CSS file of module.
	 *	@return void
	 */
	protected function __onInit(): void
	{
		$this->env->getPage()->addThemeStyle( 'module.manage.forms.css' );
	}
}
