<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;
use CeusMedia\HydrogenFramework\Environment\Web as Environment;

/** @var Environment $env */
/** @var array $words */
/** @var View $view */
/** @var object $note */

$helper		= new View_Helper_TimePhraser( $this->env );

$panelLinks	= '';
if( count( $note->links ) ){
	$rows	= [];
	foreach( $note->links as $item ){
		$link	= HtmlTag::create( 'div',
			HtmlTag::create( 'small',
				HtmlTag::create( 'a', htmlentities( $item->url, ENT_QUOTES, 'UTF-8' ), [
					'href'		=> $item->url,
					'target'	=> '_blank',
				] )
			),
			array( 'class' => 'autocut' )
		);

		$label	= HtmlTag::create( 'div',
			HtmlTag::create( 'big', htmlentities( $item->title, ENT_QUOTES, 'UTF-8' ), [
				'class'	=> 'muted',
			] ),
			array( 'class' => 'autocut' )
		);
		if( $item->title ){
			$label	= HtmlTag::create( 'div',
				HtmlTag::create( 'big',
					HtmlTag::create( 'a', htmlentities( $item->title, ENT_QUOTES, 'UTF-8' ), [
						'href'		=> $item->url,
						'target'	=> '_blank',
					] )
				),
				['class' => 'autocut']
			);
		}
		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $label.$link, ['class' => 'autocut'] )
		] );
	}
	$thead	= HtmlTag::create( 'thead', $rows );
	$table	= HtmlTag::create( 'table', [$thead], ['class' => 'table table-striped table-condensed table-fixed'] );
	$panelLinks	= HtmlTag::create( 'div', [
		HtmlTag::create( 'h3', 'Links' ),
		HtmlTag::create( 'div', $table, [
			'class'	=> 'content-panel-inner'
		] ),
	], ['class' => 'content-panel content-panel-table'] );

/*	$list	= [];
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
	$list	= [];
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
$content = match( $note->format ){
	'markdown'	=> HtmlTag::create( 'div', $note->content, [
		'id'		=> 'content-format-markdown',
		'style'		=> "display: none"
	]),
	'plaintext'	=> nl2br( $note->content ),
	default		=> View_Helper_ContentConverter::render( $env, $note->content ),
};

function getShortHash( string $noteId ): string
{
	$hash	= base64_encode( $noteId );
	return str_replace( '=', '', $hash );
}

$shortHash	= getShortHash( $note->noteId );
#$config->set( 'app.base.url', 'kb.ceusmedia.de/');
#$shortUrl	= $config->get( 'app.base.url' ).'?'.$shortHash;
$shortUrl	= $env->getBaseUrl().'?'.$shortHash;


$panelInfo	= '
		<div class="content-panel">
			<h3>Informationen</h3>
			<div class="content-panel-inner">
		<!--		<a href="./?'.$shortHash.'">Kurzlink</a><br/>
				'.HtmlElements::Input( 'shortUrl', $shortUrl, 'max', TRUE ).'-->
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
	let markdown = $("#content-format-markdown");
	if(markdown.length){
		let converter = new Markdown.Converter();
		markdown.html(converter.makeHtml(markdown.html())).show();
	}
});
</script>
';
