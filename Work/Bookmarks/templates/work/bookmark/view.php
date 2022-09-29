<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconVisit	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-right icon-white'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'icon-pencil fa fa-pencil'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white fa fa-remove'] );

$urlVisit	= './work/bookmark/visit/'.$bookmark->bookmarkId;
$linkTitle	= HtmlTag::create( 'a', $bookmark->title, array(
	'href'		=> $urlVisit,
	'target'	=> '_blank',
	'class'		=> 'autocut',
) );
$linkLabel	= preg_replace( "@^[a-z]{3,6}://@", '', $bookmark->url );
$linkUrl	= HtmlTag::create( 'a', $linkLabel, array(
	'href'		=> $urlVisit,
	'target'	=> '_blank',
) );
$title	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'big', $linkTitle )
), ['class' => 'title'] );
$url	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'small', $linkUrl )
), ['class' => 'title', 'style' => 'color: green' ] );
$description	= '';
if( $bookmark->description ){
	$description	= HtmlTag::create( 'div', array(
		HtmlTag::create( 'small', $bookmark->description )
	), ['class' => 'title'] );
}
$pageTitle	= '';
if( $bookmark->pageTitle !== $bookmark->title ){
	$pageTitle	= HtmlTag::create( 'div', array(
		HtmlTag::create( 'small', $bookmark->pageTitle, array(
			'class' => 'muted',
			'title'	=> htmlentities( $bookmark->pageDescription, ENT_QUOTES, 'utf-8' ),
		) ),
	) );
}
$pageDescription	= '';
if( $bookmark->pageDescription ){
	$pageDescription	= HtmlTag::create( 'div', array(
		HtmlTag::create( 'small', nl2br( $bookmark->pageDescription ), array(
			'class' => 'muted',
		) ),
	) );
}


$panelView	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'View' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			$title,
			$pageTitle,
			$url,
			$description,
			$pageDescription,
		), ['class' => NULL] ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'a', $iconCancel.'&nbsp;zurück', array(
				'href'		=> './work/bookmark/',
				'class'		=> 'btn btn-small'
			) ),
			HtmlTag::create( 'a', $iconVisit.'&nbsp;visit', array(
				'href'		=> './work/bookmark/visit/'.$bookmark->bookmarkId,
				'target'	=> '_blank',
				'class'		=> 'btn btn-small btn-info'
			) ),
			HtmlTag::create( 'a', $iconEdit.'&nbsp;edit', array(
				'href'		=> './work/bookmark/edit/'.$bookmark->bookmarkId,
				'class'		=> 'btn btn-small'
			) ),
			HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
				'href'		=> './work/bookmark/remove/'.$bookmark->bookmarkId,
				'class'		=> 'btn btn-small btn-danger'
			) ),
		), ['class' => 'buttonbar'] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );


$panelInfo	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Info' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'dl', array(
			HtmlTag::create( 'dt', array(
				'createdAt'
			), ['class' => ''] ),
			HtmlTag::create( 'dd', array(
				date( 'd.m.Y H:i:s', $bookmark->createdAt )
			), ['class' => ''] ),
			HtmlTag::create( 'dt', array(
				'modifiedAt'
			), ['class' => ''] ),
			HtmlTag::create( 'dd', array(
				$bookmark->modifiedAt ? date( 'd.m.Y H:i:s', $bookmark->modifiedAt ) : '-'
			), ['class' => ''] ),
			HtmlTag::create( 'dt', array(
				'visits'
			), ['class' => ''] ),
			HtmlTag::create( 'dd', array(
				$bookmark->visits
			), ['class' => ''] ),
		), ['class' => 'not-dl-horizontal'] ),
/*		HtmlTag::create( 'div', array(
		), ['class' => 'buttonbar'] ),*/
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

$list	= 'keine kommentare';
if( count( $comments ) ){
	$list	= [];
	foreach( $comments as $comment ){
		$list[]	= HtmlTag::create( 'li', $comment->content );
	}
	$list	= HtmlTag::create( 'ul', $list );
}

$panelComments	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Commments' ),
	HtmlTag::create( 'div', array(
		$list,
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'form', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'div', array(
						HtmlTag::create( 'textarea', NULL, array(
							'type'	=> 'text',
							'name'	=> 'comment',
							'class'	=> 'span12',
						) ),
					), ['class' => 'span10'] ),
					HtmlTag::create( 'div', array(
						HtmlTag::create( 'button', 'save', array(
							'type'	=> 'submit',
							'name'	=> 'save',
							'class'	=> 'btn btn-primary',
						) )
					), ['class' => 'span2'] ),
				), ['class' => 'row-fluid'] ),
			), ['action' => './work/bookmark/comment/'.$bookmark->bookmarkId, 'method' => 'post'] ),
		), ['class' => 'buttonbar'] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

$list	= 'keine kommentare';
if( count( $tags ) ){
	$list	= [];
	foreach( $tags as $tag ){
		$list[]	= HtmlTag::create( 'li', $tag->title );
	}
	$list	= HtmlTag::create( 'ul', $list );
}

$panelTags	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Tags' ),
	HtmlTag::create( 'div', array(
		$list,
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'form', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'div', array(
						HtmlTag::create( 'input', NULL, array(
							'type'	=> 'text',
							'name'	=> 'tag',
							'class'	=> 'span12',
						) ),
					), ['class' => 'span8'] ),
					HtmlTag::create( 'div', array(
						HtmlTag::create( 'button', 'save', array(
							'type'	=> 'submit',
							'name'	=> 'save',
							'class'	=> 'btn btn-primary',
						) )
					), ['class' => 'span4'] ),
				), ['class' => 'row-fluid'] ),
			), ['action' => './work/bookmark/addTag/'.$bookmark->bookmarkId, 'method' => 'post'] ),
		), ['class' => 'buttonbar'] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );


return HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		$panelView,
		$panelComments,
	), ['class' => 'span9'] ),
	HtmlTag::create( 'div', array(
		$panelInfo,
		$panelTags,
	), ['class' => 'span3'] ),
), ['class' => 'row-fluid'] ).'
<style>
small>a {
	color: green;
}
</style>';
