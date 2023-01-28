<?php
class View_Manage_My_Mangopay_Card_Registration extends View_Manage_My_Mangopay
{
	public function index(): void
	{
		$pathJs		= $this->env->getConfig()->get( 'path.scripts' );
		$urlScript	= $pathJs.'module.manage.my.mangopay.card.registration.js';
		$this->env->getPage()->js->addUrl( $urlScript );
	}
}
