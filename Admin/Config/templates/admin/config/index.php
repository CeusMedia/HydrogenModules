<?php

$list	= array();
foreach( $config as $key => $value ){
	$list[]	= UI_HTML_Tag::create( 'li', '<b>'.$key.': </b>'.$value );
}
$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'not-unstyled' ) );
return $words['index']['heading'].'
'.$list.'

';
