<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var Entity_Work_Meeting[] $meetings */

$list	= [];
foreach( $meetings as $meeting ){
	$list[]	= HtmlTag::create( 'div', $meeting->title );
}
return HtmlTag::create( 'div', $list );