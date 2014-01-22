<?php

#print_m( $threads );
#die;

$optStatus	= $words['states-thread'];
$optStatus	= UI_HTML_Elements::Options( $optStatus, (int) $request->get( 'status' ) );

$optType	= $words['types'];
$optType	= UI_HTML_Elements::Options( $optType, (int) $request->get( 'type' ) );

$list		= '<em><small class="muted">Keine.</small></em>';
if( $threads ){
	$list	= array();
	foreach( $threads as $thread ){
		$url	= './info/forum/thread/'.$thread->threadId;
		$link	= UI_HTML_Tag::create( 'a', $thread->title, array( 'href' => $url ) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list );
}

return '
<h3>Forum</h3>
<h4>Themen</h4>
'.$list.'
<br/>
<h4>Neues Thema</h4>
<form action="./info/forum/addThread" method="post">
	<div class="row-fluid">
		<div class="span6">
			<label for="input_title">Title</label>
			<input type="text" name="title" id="input_title" class="span12" value=""/>
		</div>
		<div class="span3">
			<label for="input_type">Type</label>
			<select name="type" id="input_type" class="span12">'.$optType.'</select>
		</div>
		<div class="span3">
			<label for="input_status">Title</label>
			<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
		</div>
	</div>
	<div class="buttonbar">
		<a href="./info/forum" class="btn btn-small"><i class="icon-arrow-left"></i> zurück</a>
		<button type="submit" name="save" value="1" class="btn btn-success btn-small"><i class="icon-ok icon-white"></i> speichern</button>
	</div>
</form>
';
?>