<?php
$helper		= new View_Helper_TimePhraser( $this->env );

$panelLinks	= '';
if( count( $note->links ) ){
	$rows	= array();
	foreach( $note->links as $item ){
		$link	= UI_HTML_Tag::create( 'div',
			UI_HTML_Tag::create( 'small',
				UI_HTML_Tag::create( 'a', htmlentities( $item->url, ENT_QUOTES, 'UTF-8' ), array(
					'href'		=> $item->url,
					'target'	=> '_blank',
				) )
			),
			array( 'class' => 'autocut' )
		);

		$label	= UI_HTML_Tag::create( 'div',
			UI_HTML_Tag::create( 'big', htmlentities( $item->title, ENT_QUOTES, 'UTF-8' ), array(
				'class'	=> 'muted',
			) ),
			array( 'class' => 'autocut' )
		);
		if( $item->title ){
			$label	= UI_HTML_Tag::create( 'div',
				UI_HTML_Tag::create( 'big',
					UI_HTML_Tag::create( 'a', htmlentities( $item->title, ENT_QUOTES, 'UTF-8' ), array(
						'href'		=> $item->url,
						'target'	=> '_blank',
					) )
				),
				array( 'class' => 'autocut' )
			);
		}
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $label.$link, array( 'class' => 'autocut' ) )
		) );
	}
	$thead	= UI_HTML_Tag::create( 'thead', $rows, array() );
	$table	= UI_HTML_Tag::create( 'table', array( $thead ), array( 'class' => 'table table-striped table-condensed table-fixed' ) );
	$panelLinks	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h3', 'Links' ),
		UI_HTML_Tag::create( 'div', $table, array(
			'class'	=> 'content-panel-inner'
		) ),
	), array( 'class' => 'content-panel content-panel-table' ) );

/*	$list	= array();
	foreach( $note->links as $item ){
		$label	= '<span class="link untitled">'.$item->url.'</span>';
		if( $item->title )
			$label	= '<span class="link titled"><acronym title="'.$item->url.'">'.htmlentities( $item->title, ENT_QUOTES, 'UTF-8' ).'</acronym></span>';
		$url	= './work/note/link/'.$item->linkId;
		$link	= '<a href="'.$url.'">'.$label.'</a>';
		$list[]	= '<li class="link">'.$link.'</li>';
	}
	$listLinks	= '<ul class="links-list">'.join( $list ).'</ul><div style="clear: left"></div>';
	$panelLinks	= '
		<div class="content-panel">
			<h3>Links</h3>
			<div class="content-panel-inner">
				<div class="note-links">'.$listLinks.'</div>
			</div>
		</div>';*/
}

$panelTags	= '';
if( count( $note->tags ) ){
	$list	= array();
	foreach( $note->tags as $tag ){
		$label	= '<span class="tag">'.htmlentities( $tag->content, ENT_QUOTES, 'UTF-8' ).'</span>';
		$list[]	= '<li class="tag">'.$label.'</li>';
	}
	$listTags	= '<ul class="tags-list">'.join( $list ).'</ul><div style="clear: left"></div>';
	$panelTags	= '
		<div class="content-panel">
			<h3>Tags</h3>
			<div class="content-panel-inner">
				<div class="note-tags">'.$listTags.'</div>
			</div>
		</div>';
}

#$converter	= new View_Helper_ContentConverter();
switch( $note->format ){
	case 'markdown':
		$content	= UI_HTML_Tag::create( 'div', $note->content, array( 'id' => 'content-format-markdown', 'style' => "display: none" ) );
		break;
	case 'plaintext':
		$content	= nl2br( $note->content );
		break;
	case 'content':
	default:
		$content	= View_Helper_ContentConverter::render( $env, $note->content );
}

function getShortHash( $noteId ){
	$hash	= base64_encode( $noteId );
	$hash	= str_replace( '=', '', $hash );
	return $hash;
}

$shortHash	= getShortHash( $note->noteId );
#$config->set( 'app.base.url', 'kb.ceusmedia.de/');
$shortUrl	= $config->get( 'app.base.url' ).'?'.$shortHash;


$panelInfo	= '
		<div class="content-panel">
			<h3>Informationen</h3>
			<div class="content-panel-inner">
		<!--		<a href="./?'.$shortHash.'">Kurzlink</a><br/>
				'.UI_HTML_Elements::Input( NULL, $shortUrl, 'max', TRUE ).'-->
				<dl class="dl-horizontal">
					<dt>erstellt</dt>
					<dd>vor '.$helper->convert( $note->createdAt, TRUE ).'</dd>
					<dt>von</dt>
					<dd> '.$note->user->username.'</dd>
					<dt>zuletzt verändert</dt>
					<dd>vor '.$helper->convert( $note->modifiedAt, TRUE ).'</dd>
					<dt><a href="./work/note/view/'.$note->noteId.'">Link</a></dt>
					<dd><a href="./work/note/view/'.$note->noteId.'" class="btn btn-mini" target="_blank">in neuem Tab</a></dd>
					<dt>Notiz-Textverweis</dt>
					<dd><code>[note:'.$note->noteId.']</code></dd>
					<dt>Views</dt>
					<dd>'.$note->numberViews.'</dd>
				</dl>
			</div>
		</div>
';

return '
<small><a href="./work/note">&laquo;&nbsp;zurück</a></small>
<div class="row-fluid">
	<div class="span8">
		<div class="content-panel">
			<h3>'.htmlentities( $note->title, ENT_QUOTES, 'UTF-8' ).'</h3>
			<div class="content-panel-inner">
				<div class="note-view-content">
'.$content.'
				</div><br/>
				<div class="buttonbar">
					<a href="./work/note" class="btn not-btn-small"><i class="icon-arrow-left"></i> zur Liste</a>
					<a href="./work/note/edit/'.$note->noteId.'" class="btn not-btn-small btn-primary"><i class="icon-pencil icon-white"></i> bearbeiten</a>
				</div>
			</div>
		</div>
	</div>
	<div class="span4">
		'.$panelInfo.'
		'.$panelTags.'
		'.$panelLinks.'
	</div>
</div>
<script>
$(document).ready(function(){
	var markdown = $("#content-format-markdown");
	if(markdown.size()){
		var converter = new Markdown.Converter();
		markdown.html(converter.makeHtml(markdown.html())).show();
	}
});
</script>
';
?>
