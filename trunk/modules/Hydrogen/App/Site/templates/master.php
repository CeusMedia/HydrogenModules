<?php

$pathCDN	= "http://cdn.int1a.net/";

$links		= array(
	''			=> "Start",
	'manage/page'	=> 'Seiten',
);

$controller	= $this->env->getRequest()->get( 'controller' );
$current	= CMF_Hydrogen_View_Helper_Navigation_SingleList::getCurrentKey( $links, $controller );

$list	= array();
foreach( $links as $key => $value ){
	$link	= UI_HTML_Tag::create( 'a', $value, array( 'href' => './'.$key ) );
	$class	= $key == $current ? "active" : NULL;
	$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
}

$list	= UI_HTML_Tag::create( 'ul', $list, array( "class" => "nav" ) );

$body	= '
<div id="layout-container">
	<div class="nav navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<span href="../" class="brand">My Project</span>
				'.$list.'
			</div>
		</div>
	</div>
	<div class="container" style="margin-top: 50px">
		<div id="layout-messenger">'.$messenger->buildMessages().'</div>
		<div id="layout-content">
			'.$content.'
		</div>
	</div>
</div>';

$page->addBody( $body );
return $page->build();
?>
