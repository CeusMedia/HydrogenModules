<?php

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var array<string,array<string,string>> $words */
/** @var View_Work_Issue $view */
/** @var object[] $issues */
/** @var int $page */
/** @var int $total */
/** @var int $number */

$helper	= new View_Helper_TimePhraser( $env );

$rows	= [];
foreach( $issues as $issue ){
	$reporter	= '';
	$manager	= '';
	if( $issue->reporterId && isset( $users[$issue->reporterId] ) ){
		$link		= HtmlTag::create( 'a', $users[$issue->reporterId]->username, ['href' => './manage/user/edit/'.$issue->reporterId] );
		$reporter	= HtmlTag::create( 'span', $link, ['class' => 'role role'.$users[$issue->reporterId]->roleId] );
	}
	if( $issue->managerId && isset( $users[$issue->managerId] ) ){
		$link		= HtmlTag::create( 'a', $users[$issue->managerId]->username, ['href' => './manage/user/edit/'.$issue->managerId] );
		$manager	= HtmlTag::create( 'span', $link, ['class' => 'role role'.$users[$issue->managerId]->roleId] );
	}
	$notes		= count( $issue->notes );
	$changes	= count( $issue->changes );
	$changes	= ( $notes || $changes ) ? '<small class="muted">mit '.$changes.' Veränderung(en) und '.$notes.' Notiz(en)</small>' : '';
	$link		= HtmlElements::Link( './work/issue/edit/'.$issue->issueId, $issue->title, 'issue-title' );
	$type		= HtmlTag::create( 'span', $words['types'][$issue->type], ['class' => 'issue-type type-'.$issue->type] );
	$severity	= HtmlTag::create( 'span', $words['severities'][$issue->severity], ['class' => 'issue-severity severity-'.$issue->severity] );
	$priority	= HtmlTag::create( 'span', $words['priorities'][$issue->priority], ['class' => 'issue-priority priority-'.$issue->priority] );
	$status		= HtmlTag::create( 'span', $words['states'][$issue->status], ['class' => 'issue-status status-'.$issue->status] );
	$progress	= $issue->progress ? HtmlTag::create( 'span', $issue->progress.'%', ['class' => 'issue-progress progress-'.( floor( $issue->progress / 25 ) * 25 )] ) : "-";
//	$createdAt	= date( 'd.m.Y H:i:s', $issue->createdAt );
	$createdAt	= $helper->convert( $issue->createdAt, TRUE, 'vor' );
//	$modifiedAt	= $issue->modifiedAt ? date( 'd.m.Y H:i:s', $issue->modifiedAt ) : "-";
	$modifiedAt	= $helper->convert( $issue->modifiedAt ?? 0, TRUE, 'vor' );
	$rows[]	= '
<tr>
	<td>'.$link.'<br/>'.$changes.'</td>
	<td>'.$type.'<br/>'.$priority.'</td>
	<td>'.$status.'<br/>'.$progress.'</td>
	<td>'.$reporter.'<br/>'.$manager.'</td>
	<td><small>'.$createdAt.'</small><br/><small>'.$modifiedAt.'</small></td>
</tr>';
}

$pagination	= new PageControl( './work/issue', $page, ceil( $number / 10 ) );
$pagination	= $pagination->render();

return '
<div class="content-panel content-panel-list"">
	<h3>Probleme</h3>
	<div class="content-panel-inner content-panel-table">
<!--		<legend>Einträge ('.$number.' von '.$total.')</legend>-->
		<table class="table table-condensed table-striped">
			<colgroup>
				<col width="47%"/>
				<col width="10%"/>
				<col width="12%"/>
				<col width="18%"/>
				<col width="13%"/>
			</colgroup>
			<thead>
				<tr>
					<th>Kurzbeschreibung / Veränderungen</th>
					<th>Typ / Schweregrad</th>
					<th>Zustand / Fortschritt</th>
					<th>Reporter / Manager</th>
					<th>gemeldet / bearbeitet</th>
				</tr>
			</thead>
			<tbody>
				'.join( $rows ).'
			</tbody>
		</table>
		<div class="buttonbar">
			<a class="btn btn-small btn-success" href="./work/issue/add"><i class="icon-plus icon-white"></i> neuer Eintrag</a>
			'.$pagination.'
		</div>
	</div>
</div>
';
