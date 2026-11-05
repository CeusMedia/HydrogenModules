<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string,array<string,string>> $words */
/** @var object $bookmark */
/** @var array<object> $comments */
/** @var array<object> $tags */

$w		= (object) $words['view'];

$iconCancel	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconVisit	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-right icon-white'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'icon-pencil fa fa-pencil'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white fa fa-remove'] );

$urlVisit	= './work/bookmark/visit/'.$bookmark->bookmarkId;
$linkTitle	= HtmlTag::create( 'a', $bookmark->title, [
	'href'		=> $urlVisit,
	'target'	=> '_blank',
	'class'		=> 'autocut',
] );
$linkLabel	= preg_replace( "@^[a-z]{3,6}://@", '', $bookmark->url );
$linkUrl	= HtmlTag::create( 'a', $linkLabel, [
	'href'		=> $urlVisit,
	'target'	=> '_blank',
] );
$title	= HtmlTag::create( 'div', [
	HtmlTag::create( 'big', $linkTitle )
], ['class' => 'title'] );
$url	= HtmlTag::create( 'div', [
	HtmlTag::create( 'small', $linkUrl )
], ['class' => 'title', 'style' => 'color: green' ] );
$description	= '';
if( $bookmark->description ){
	$description	= HtmlTag::create( 'div', [
		HtmlTag::create( 'small', $bookmark->description )
	], ['class' => 'title'] );
}
$pageTitle	= '';
if( $bookmark->pageTitle !== $bookmark->title ){
	$pageTitle	= HtmlTag::create( 'div', [
		HtmlTag::create( 'small', $bookmark->pageTitle, [
			'class' => 'muted',
			'title'	=> htmlentities( $bookmark->pageDescription, ENT_QUOTES, 'utf-8' ),
		] ),
	] );
}
$pageDescription	= '';
if( $bookmark->pageDescription ){
	$pageDescription	= HtmlTag::create( 'div', [
		HtmlTag::create( 'small', nl2br( $bookmark->pageDescription ), [
			'class' => 'muted',
		] ),
	] );
}


$panelView	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'div', [
			$title,
			$pageTitle,
			$url,
			$description,
			$pageDescription,
		], ['class' => NULL] ),
		HtmlTag::create( 'div', [
			HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
				'href'		=> './work/bookmark/',
				'class'		=> 'btn btn-small'
			] ),
			HtmlTag::create( 'a', $iconVisit.'&nbsp;'.$w->buttonVisit, [
				'href'		=> './work/bookmark/visit/'.$bookmark->bookmarkId,
				'target'	=> '_blank',
				'class'		=> 'btn btn-small btn-info'
			] ),
			HtmlTag::create( 'a', $iconEdit.'&nbsp;'.$w->buttonEdit, [
				'href'		=> './work/bookmark/edit/'.$bookmark->bookmarkId,
				'class'		=> 'btn btn-small'
			] ),
			HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, [
				'href'		=> './work/bookmark/remove/'.$bookmark->bookmarkId,
				'class'		=> 'btn btn-small btn-danger'
			] ),
		], ['class' => 'buttonbar'] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );


$wi		= (object) $words['view.info'];
$panelInfo	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $wi->heading ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'dl', [
			HtmlTag::create( 'dt', [
				$wi->labelCreatedAt
			], ['class' => ''] ),
			HtmlTag::create( 'dd', [
				date( 'd.m.Y H:i:s', $bookmark->createdAt )
			], ['class' => ''] ),
			HtmlTag::create( 'dt', [
				$wi->labelModifiedAt
			], ['class' => ''] ),
			HtmlTag::create( 'dd', [
				$bookmark->modifiedAt ? date( 'd.m.Y H:i:s', $bookmark->modifiedAt ) : '-'
			], ['class' => ''] ),
			HtmlTag::create( 'dt', [
				$wi->labelVisits
			], ['class' => ''] ),
			HtmlTag::create( 'dd', [
				$bookmark->visits
			], ['class' => ''] ),
		], ['class' => 'not-dl-horizontal'] ),
/*		HtmlTag::create( 'div', [
		], ['class' => 'buttonbar'] ),*/
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );

$wc		= (object) $words['view.comments'];
$list	= $wc->noEntries;
if( count( $comments ) ){
	$list	= [];
	foreach( $comments as $comment ){
		$list[]	= HtmlTag::create( 'li', $comment->content );
	}
	$list	= HtmlTag::create( 'ul', $list );
}

$panelComments	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $wc->heading ),
	HtmlTag::create( 'div', [
		$list,
		HtmlTag::create( 'div', [
			HtmlTag::create( 'form', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'div', [
						HtmlTag::create( 'textarea', NULL, [
							'type'	=> 'text',
							'name'	=> 'comment',
							'class'	=> 'span12',
						] ),
					], ['class' => 'span10'] ),
					HtmlTag::create( 'div', [
						HtmlTag::create( 'button', $wc->buttonSave, [
							'type'	=> 'submit',
							'name'	=> 'save',
							'class'	=> 'btn btn-primary',
						] )
					], ['class' => 'span2'] ),
				], ['class' => 'row-fluid'] ),
			], ['action' => './work/bookmark/comment/'.$bookmark->bookmarkId, 'method' => 'post'] ),
		], ['class' => 'buttonbar'] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );

$wt		= (object) $words['view.tags'];
$list	= $wt->noEntries;
if( count( $tags ) ){
	$list	= [];
	foreach( $tags as $tag ){
		$list[]	= HtmlTag::create( 'li', $tag->title );
	}
	$list	= HtmlTag::create( 'ul', $list );
}

$panelTags	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $wt->heading ),
	HtmlTag::create( 'div', [
		$list,
		HtmlTag::create( 'div', [
			HtmlTag::create( 'form', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'div', [
						HtmlTag::create( 'input', NULL, [
							'type'	=> 'text',
							'name'	=> 'tag',
							'class'	=> 'span12',
						] ),
					], ['class' => 'span8'] ),
					HtmlTag::create( 'div', [
						HtmlTag::create( 'button', $wt->buttonSave, [
							'type'	=> 'submit',
							'name'	=> 'save',
							'class'	=> 'btn btn-primary',
						] )
					], ['class' => 'span4'] ),
				], ['class' => 'row-fluid'] ),
			], ['action' => './work/bookmark/addTag/'.$bookmark->bookmarkId, 'method' => 'post'] ),
		], ['class' => 'buttonbar'] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );


return HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		$panelView,
		$panelComments,
	], ['class' => 'span9'] ),
	HtmlTag::create( 'div', [
		$panelInfo,
		$panelTags,
	], ['class' => 'span3'] ),
], ['class' => 'row-fluid'] ).'
<style>
small>a {
	color: green;
}
</style>';
