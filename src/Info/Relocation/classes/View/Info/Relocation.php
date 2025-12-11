<?php

use CeusMedia\HydrogenFramework\View;

class View_Info_Relocation extends View
{
	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function fail(): string
	{
		return $this->loadContentFile( 'html/info/relocation/fail.html' );
	}

	/**
	 *	@return		void
	 */
	public function index(): void
	{
	}
}
