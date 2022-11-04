<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Tool_Calculator extends Hook
{
	public static function onPageBuild( Environment $env, $module, $context, $payload )
	{
		$payload	= (object) $payload;
		$helper		= new View_Helper_Tool_Calculator( $env );
		$helper->setId( 'calc-modal' );
		$env->getPage()->js->addScriptOnReady( 'prepareCalculatorLink();' );
		$payload->content	.= HtmlTag::create( 'div', $helper->render(), array(
			'id'				=> 'modalCalculator',
			'class'				=> 'modal hide',
			'tabindex'			=> '-1',
			'role'				=> 'dialog',
			'aria-labelledby'	=> 'myModalLabel',
			'aria-hidden'		=> 'true',
		) );
	}
}
