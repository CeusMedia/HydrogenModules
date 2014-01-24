<?php
$helper			= new View_Helper_TimePhraser( $env );
$iconApprove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$table	= '<em><small class="muted">Keine.</small></em>';
if( $posts ){
	$rows	= array();
	foreach( $posts as $post ){
		$buttonApprove	= "";
		$buttonRemove	= "";
		if( (int) $post->status === 0 ){
			if( in_array( 'approvePost', $rights ) )
				$buttonApprove	= UI_HTML_Tag::create( 'a', $iconApprove, array(
					'href'	=> './info/forum/approvePost/'.$post->postId,
					'class'	=> 'btn btn-small btn-success',
					'title'	=> $words['thread']['buttonApprove']
				) );
			if( in_array( 'removePost', $rights ) )
				$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
					'href'	=> './info/forum/removePost/'.$post->postId,
					'class'	=> 'btn btn-small btn-danger',
					'title'	=> $words['thread']['buttonRemove']
				) );
		}
		$cells	= array(
			UI_HTML_Tag::create( 'td', $post->content ),
			UI_HTML_Tag::create( 'td', $buttonApprove.$buttonRemove ),
		);
		$rows[]	= UI_HTML_Tag::create( 'tr', $cells );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( '80%', '20%' );
	$heads		= UI_HTML_Elements::TableHeads( array() );
	$thead		= UI_HTML_Tag::create( 'tbody', $heads );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

$optParent	= array();
$optParent	= UI_HTML_Elements::Options( $optParent );

return '
<h3><span class="muted">Forum: </span>'.$thread->title.'</h3>
<div class="row-fluid">
	<div class="span8">
		<h4>Beiträge</h4>
		'.$table.'
		<br/>
	</div>
	<div class="span4">
		<h4>Neuer Beitrag</h4>
		<form action="./info/forum/addPost/'.$thread->threadId.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_content">Inhalt</label>
					<textarea name="content" id="input_content" rows="10" class="span12">'.$request->get( 'content' ).'</textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_parentId">als Antwort auf</label>
					<select name="parentId" id="input_parentId" class="span12">'.$optParent.'</select>
					
				</div>
			</div>
			<div class="buttonbar">
				<a href="./info/forum" class="btn btn-small"><i class="icon-arrow-left"></i> zurück</a>
				<button type="submit" name="save" value="1" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
			</div>
		</form>
	</div>
</div>
';

?>