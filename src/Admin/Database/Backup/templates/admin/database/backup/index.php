<?php

use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $backups */
/** @var string $currentCopyPrefix */

$w				= (object) $words['index'];

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$list	= HtmlTag::create( 'div', $w->noEntries, ['class' => 'alert alert-info'] );
if( $backups ){
	$list	= [];
	foreach( $backups as $backup ){
		if( is_string( $backup->comment ) ){
			$backup->comment	= [
				'comment'		=> $backup->comment,
			];
		}
		else if( is_null( $backup->comment ) ){
			$backup->comment	= ['comment' => ''];
		}
		$rowClass	= '';
		$status		= '';
		if( !empty( $backup->comment['copyPrefix'] ) ){
			$rowClass	= 'info';
			$status		= $w->statusCopyInstalled;
			if( $backup->comment['copyPrefix'] === $currentCopyPrefix ){
				$rowClass	= 'success';
				$status		= $w->statusCopyActivated;
			}
		}

		$link	= HtmlTag::create( 'a', $backup->filename, ['href' => './admin/database/backup/view/'.$backup->id] );
		if( class_exists ( 'View_Helper_TimePhraser' ) ){
			$helper			= new View_Helper_TimePhraser( $env );
			$creationDate	= $helper->convert( $backup->timestamp, TRUE, $w->timePrefix, $w->timeSuffix );
		}
		else {
			$date			= HtmlTag::create( 'span', date( 'd.m.Y', $backup->timestamp ), ['class' => ''] );
			$time			= HtmlTag::create( 'small', date( 'H:i:s', $backup->timestamp ), ['class' => 'muted'] );
			$creationDate	= $date.' '.$time;
		}
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $status ),
			HtmlTag::create( 'td', HtmlTag::create( 'small', $backup->comment['comment'], ['class' => 'muted'] ) ),
			HtmlTag::create( 'td', UnitFormater::formatBytes( $backup->filesize ) ),
			HtmlTag::create( 'td', $creationDate ),
		], ['class' => $rowClass] );
	}
	$colgroup	= HtmlElements::ColumnGroup( ['33%', '15%', '', '100px', '150px'] );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [$w->headFile, $w->headStatus, $w->headComment, $w->headSize, $w->headCreatedAt] ) );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-fixed'] );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' '.$w->buttonAdd, [
	'href'	=> './admin/database/backup/backup',
	'class'	=> 'btn btn-success',
] );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
