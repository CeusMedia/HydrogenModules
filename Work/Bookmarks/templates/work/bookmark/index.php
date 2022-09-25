<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$table	= '<div class="alert alert-warning">No entries found.</div>';
if( $bookmarks ){
	$iconVisit	= HtmlTag::create( 'i', '', array( 'class' => 'icon-arrow-right' ) );
	$iconView	= HtmlTag::create( 'i', '', array( 'class' => 'icon-eye-open fa fa-eye' ) );
	$iconEdit	= HtmlTag::create( 'i', '', array( 'class' => 'icon-pencil fa fa-pencil' ) );
	$rows	= [];
	foreach( $bookmarks as $bookmark ){
		$urlVisit	= './work/bookmark/visit/'.$bookmark->bookmarkId;
		$urlView	= './work/bookmark/view/'.$bookmark->bookmarkId;
		$linkTitle	= HtmlTag::create( 'a', $bookmark->title, array(
			'href'		=> $urlView,
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
		), array( 'class' => 'title' ) );
		$url	= HtmlTag::create( 'div', array(
			HtmlTag::create( 'small', $linkUrl )
		), array( 'class' => 'title', 'style' => 'color: green'  ) );
		$description	= '';
		if( $bookmark->description ){
			$description	= HtmlTag::create( 'div', array(
				HtmlTag::create( 'small', $bookmark->description )
			), array( 'class' => 'title' ) );
		}
		$pageTitle	= '';
		if( $bookmark->pageTitle !== $bookmark->title ){
			$pageTitle	= HtmlTag::create( 'div', array(
				HtmlTag::create( 'small', $bookmark->pageTitle, array(
					'class' => 'muted',
				) ),
			) );
		}

		$tags	= [];
		foreach( $bookmark->tags as $tag ){
			$tags[]	= HtmlTag::create( 'span', $tag->title, array( 'class' => 'label label-info' ) );
		}
		$tags	= HtmlTag::create( 'ul', $tags, array( 'class' => 'inline list-inline' ) );

		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', array(
				$title,
				$pageTitle,
				$url,
				$description,
				$tags,
			), array( 'class' => NULL ) ),
			HtmlTag::create( 'td', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'a', $iconVisit, array(
						'href'		=> './work/bookmark/visit/'.$bookmark->bookmarkId,
						'target'	=> '_blank',
						'class'		=> 'btn btn-mini',
						'title'		=> 'visit',
					) ),
					'<br/>',
					HtmlTag::create( 'a', $iconView, array(
						'href'		=> './work/bookmark/view/'.$bookmark->bookmarkId,
						'class'		=> 'btn btn-mini',
						'title'		=> 'details',
					) ),
					'<br/>',
					HtmlTag::create( 'a', $iconEdit, array(
						'href'		=> './work/bookmark/edit/'.$bookmark->bookmarkId,
						'class'		=> 'btn btn-mini',
						'title'		=> 'edit',
					) ),
				), array( 'class' => 'not-btn-group pull-right' ) ),
			), array( 'class' => NULL ) ),
		), array() );
	}
	$table	= HtmlTag::create( 'table', $rows, array( 'class' => 'table table-striped' ) );
}

$panelList	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Table' ),
	HtmlTag::create( 'div', array(
		$table
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel content-panel-list content-panel-table' ) );


$form	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Table' ),
	HtmlTag::create( 'div', array(
		$table
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel content-panel-list content-panel-table' ) );


$panelAdd	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Add' ),
	HtmlTag::create( 'div', '
		<form action="./work/bookmark/add" method="POST">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_url">URL</label>
					<input type="text" name="url" id="input_url" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary">save</button>
			</div>
		</form>
	', array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel content-panel-form' ) );


$panelFilter	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Filter' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'input', NULL, array(
						'type'	=> 'text',
						'name'	=> 'query',
						'class'	=> 'span12',
						'value'	=> htmlentities( $filterQuery, ENT_QUOTES, 'UTF-8' ),
					) ),
				), array( 'class' => 'span8' ) ),
				HtmlTag::create( 'div', array(
				), array( 'class' => 'span4' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'button', 'filter', array(
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-small btn-primary',
				) ),
				HtmlTag::create( 'a', 'reset', array(
					'href'	=> './work/bookmark/filter/reset',
					'class'	=> 'btn btn-small btn-inverse',
				) ),
			), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './work/bookmark/filter', 'method' => 'post' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel content-panel-form' ) );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		$panelFilter,
	), array( 'class' => 'span3' ) ),
	HtmlTag::create( 'div', array(
		$panelList,
	), array( 'class' => 'span6' ) ),
	HtmlTag::create( 'div', array(
		$panelAdd,
	), array( 'class' => 'span3' ) ),
), array( 'class' => 'row-fluid' ) ).'
<style>
small>a {
	color: green;
}
</style>';
