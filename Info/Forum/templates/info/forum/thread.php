<?php

$iconApprove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );


$list	= '<em><small class="muted">Keine.</small></em>';
if( $posts ){
	$list	= array();
	foreach( $posts as $post ){
		$buttonApprove	= "";
		$buttonRemove	= "";
		if( (int) $post->status === 0 ){
			$buttonApprove	= UI_HTML_Tag::create( 'a', $iconApprove, array( 'href' => './info/forum/approvePost/'.$post->postId, 'class' => 'btn btn-mini btn-success' ) );
			$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array( 'href' => './info/forum/removePost/'.$post->postId, 'class' => 'btn btn-mini btn-danger' ) );
		}
		$list[]	= UI_HTML_Tag::create( 'tr', '<td>'.$post->content.'</td><td>'.$buttonApprove.$buttonRemove.'</td>' );
	}
	$list	= UI_HTML_Tag::create( 'table', $list );
}


return '
<h3><span class="muted">Forum: </span>'.$thread->title.'</h3>	
<h4>Beiträge</h4>
'.$list.'
<br/>
<h4>Neuer Beitrag</h4>
<form action="./info/forum/addPost/'.$thread->threadId.'" method="post">
	<div class="row-fluid">
		<div class="span12">
			<label for="input_content">Inhalt</label>
			<textarea name="content" id="input_content" rows="10" class="span12">'.$request->get( 'content' ).'</textarea>
		</div>
	</div>
	<div class="buttonbar">
		<button type="submit" name="save" value="1" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
	</div>
</form>';

?>