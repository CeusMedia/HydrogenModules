<?php

use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\HydrogenFramework\Environment\Web as Env;

/** @var Env $env */
/** @var object $issue */

try{
	if( !$issue->notes )
		return;

	$helper	= new View_Helper_Work_Issue_Changes( $env );
	$helper->setIssue( $issue );
	$list	= $helper->render();

	return '
	<div class="content-panel">
		<h3>Entwicklung</h3>
		<div class="content-panel-inner">
			'.$list.'
		</div>
	</div>';
}
catch( Throwable $e ){
	HtmlExceptionPage::display( $e);
}
