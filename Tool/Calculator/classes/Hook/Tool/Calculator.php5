<?php
class Hook_Tool_Calculator extends CMF_Hydrogen_Hook
{
	public static function onPageBuild( CMF_Hydrogen_Environment $env, $module, $context, $payload )
	{
		$payload	= (object) $payload;
		$helper		= new View_Helper_Tool_Calculator( $env );
		$helper->setId( 'calc-modal' );
		$env->getPage()->js->addScriptOnReady( 'prepareCalculatorLink();' );
		$payload->content	.= UI_HTML_Tag::create( 'div', $helper->render(), array(
			'id'				=> 'modalCalculator',
			'class'				=> 'modal hide',
			'tabindex'			=> '-1',
			'role'				=> 'dialog',
			'aria-labelledby'	=> 'myModalLabel',
			'aria-hidden'		=> 'true',
		) );
	}
}
