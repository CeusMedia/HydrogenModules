<?php
class View_Manage_My_Mangopay_Bank_Payin extends View_Manage_My_Mangopay{

	public function add(){}

	public function index(){
		$pathJs		= $this->env->getConfig()->get( 'path.scripts' );
		$urlScript	= $pathJs.'module.manage.my.mangopay.bank.payin.js';
		$this->env->getPage()->js->addUrl( $urlScript );

	}

	public function view(){}
}
