<?php
$w			= (object) $words['index-list'];

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
$iconGo		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-right' ) );

$table	= '<div class="muted"><em><small class="muted">'.$w->noEntries.'</small></em></div>';

if( $relocations ){
	$rows	= array();
	foreach( $relocations as $relocation ){
		$class	= "warning";
		if( $relocation->status > 0 )
			$class	= "success";
		else if( $relocation->status < 0 )
			$class	= "error";
		$uri	= "./manage/relocation/edit/".$relocation->relocationId;
		$uri	= "./manage/relocation/edit/".$relocation->relocationId;
		$link	= UI_HTML_Tag::create( 'a', $relocation->title, array( 'href' => $uri ) );
		$buttonEdit	= UI_HTML_Tag::create( 'a', $iconEdit, array(
			'href'	=> $uri,
			'class'	=> 'btn btn-small',
			'title'	=> $w->buttonEdit,
		) );
		$buttonGo	= UI_HTML_Tag::create( 'a', $iconGo, array(
			'href'	=> $relocation->url,
			'class'	=> 'btn btn-small',
			'title'	=> $w->buttonGo,
		) );
		$buttons	= UI_HTML_Tag::create( 'div', array( $buttonEdit, $buttonGo ), array( 'class' => 'btn-group' ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $relocation->relocationId ),
			UI_HTML_Tag::create( 'td', $link.'<br/><small class="autocut">'.$relocation->url.'</small>' ),
			UI_HTML_Tag::create( 'td', $relocation->views ),
			UI_HTML_Tag::create( 'td', $buttons ),
		), array(
			'data-status'	=> $relocation->status,
			'data-url'		=> $relocation->url,
			'class'			=> $class
		) );
	}
	$columns	= UI_HTML_Elements::ColumnGroup( "50px", "", "80px", "100px" );
	$heads	= UI_HTML_Elements::TableHeads( array( $w->headId, $w->headTitle, $w->headViews, $w->headActions ) );
	$thead	= UI_HTML_Tag::create( 'thead', $heads );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', $columns.$thead.$tbody, array( 'class' => 'table' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;hinzufügen', array(
	'href'	=> './manage/relocation/add',
	'class'	=> 'btn btn-success'
) );

$abbrCount	= UI_HTML_Tag::create( 'abbr', $count, array( 'title' => $w->titleCount ) );
$abbrTotal	= UI_HTML_Tag::create( 'abbr', $total, array( 'title' => $w->titleTotal ) );

return '
		<div class="content-panel">
			<h3>Einträge <small>('.$abbrCount.'/'.$abbrTotal.')</small></h3>
			<div class="content-panel-inner">
				'.$table.'
			</div>
			'.$buttonAdd.'
		</div>
';
