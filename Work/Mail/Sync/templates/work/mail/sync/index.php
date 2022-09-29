<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$table	= '<div class="alert alert-info">Keine.</div>';
if( $hosts ){
	$list	= [];
	foreach( $hosts as $host ){
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $host->host ),
			HtmlTag::create( 'td', $host->ip ),
		) );
	}
	$table	= HtmlTag::create( 'table', $list, ['class' => 'table table-fixed'] );
}
$panelHosts	= '<div class="content-panel">
	<h3>Hosts</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			<a href="./work/mail/sync/addHost" class="btn btn-success"><i class="fa fa-fw fa-plus"></i>&nbsp;neuer Host</a>
		</div>
	</div>
</div>';

$statusLabels	= array(
	-1		=> "Fehler",
	0		=> "neu",
	1		=> "bereit",
	2		=> "in Arbeit",
	3		=> "synchronisiert",
	4		=> "abgeschlossen",
);
$statusClasses	= array(
	-1		=> "progress-danger",
	0		=> "",
	1		=> "progress-warning",
	2		=> "progress-warning progress-striped",
	3		=> "progress-success",
	4		=> "progress-info",
);

$helperTimestamp	= new View_Helper_TimePhraser( $env );

$table	= '<div class="alert alert-info">Keine.</div>';
if( $syncs ){
	$list	= [];
	foreach( $syncs as $sync ){
		foreach( $hosts as $host )
			if( $host->mailSyncHostId == $sync->sourceMailHostId ){
				$sourceHost	= $host->host ? $host->host : $host->ip;
				break;
			}
		foreach( $hosts as $host )
			if( $host->mailSyncHostId == $sync->targetMailHostId ){
				$targetHost	= $host ? $host->host : $host->ip;
				break;
			}

		$statusClass	= '';
		$statusLabel	= $statusLabels[$sync->status];
		if( $sync->status == Model_Mail_Sync::STATUS_ERROR )
			$statusLabel	= HtmlTag::create( 'acronym', $statusLabel, ['title' => $sync->run->message] );
		$status	= HtmlTag::create( 'div', array(
			HtmlTag::create( 'div', $statusLabel, array(
				'class'	=> 'bar',
				'style'	=> 'width: 100%',
			) )
		), array(
			'class'	=> 'progress '.$statusClasses[$sync->status],
		) );

		$iconActivate	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-toggle-on'] );
		$iconClose		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check-square-o'] );
		$iconEdit		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
		$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

		$buttonActivate	= HtmlTag::create( 'a', $iconActivate.'&nbsp;aktivieren', ['href' => './work/mail/sync/setSyncStatus/'.$sync->mailSyncId.'/1'] );
		$buttonClose	= HtmlTag::create( 'a', $iconClose.'&nbsp;schließen', ['href' => './work/mail/sync/setSyncStatus/'.$sync->mailSyncId.'/4'] );
		$buttonEdit		= HtmlTag::create( 'a', $iconEdit.'&nbsp;bearbeiten', ['href' => './work/mail/sync/editSync/'.$sync->mailSyncId] );
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', ['href' => './work/mail/sync/removeSync/'.$sync->mailSyncId] );

		$buttons	= [];
		if( $sync->status == Model_Mail_Sync::STATUS_NEW ){
			$buttons[]	= $buttonActivate;
		}
		if( $sync->status == Model_Mail_Sync::STATUS_ERROR ){
			$buttons[]	= $buttonActivate;
			$buttons[]	= $buttonEdit;
			$buttons[]	= $buttonRemove;
		}
		else if( $sync->status == Model_Mail_Sync::STATUS_SYNCHED ){
			$buttons[]	= $buttonClose;
		}
		else if( $sync->status == Model_Mail_Sync::STATUS_CLOSED ){
			$buttons[]	= $buttonActivate;
			$buttons[]	= $buttonRemove;
		}

		foreach( $buttons as $nr => $button )
			$buttons[$nr]	= HtmlTag::create( 'li', $button );

		if( $buttons ){
			$buttons	= HtmlTag::create( 'div', array(
				HtmlTag::create( 'a', '<i class="fa fa-fw fa-cog"></i>', ['class' => 'btn btn-large dropdown-toggle', 'data-toggle' => "dropdown"] ),
				HtmlTag::create( 'ul', $buttons, ['class' => 'dropdown-menu pull-right'] )
			), ['class' => 'btn-group'] );
		}
		else{
			$buttons	= '';
		}

		$messages	= 0;
		foreach( $sync->runs as $run ){
			if( $run->status == Model_Mail_Sync_Run::STATUS_SUCCESS ){
				$statistics	= json_decode( $run->statistics, TRUE );
				if( isset( $statistics['Messages transferred'] ) )
					$messages	+= (int) $statistics['Messages transferred'];
			}
		}
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $sync->sourceUsername.'<br/><small class="muted">'.$sourceHost.'</small>'  ),
			HtmlTag::create( 'td', $sync->targetUsername.'<br/><small class="muted">'.$targetHost.'</small>'  ),
			HtmlTag::create( 'td', $status.'<br/><small class="muted">'.$helperTimestamp->convert( $sync->modifiedAt, TRUE, 'vor' ).'</small>' ),
			HtmlTag::create( 'td', HtmlTag::create( 'span', count( $sync->runs ), ['class' => 'badge '.( count( $sync->runs ) ? 'badge-info' : '' )] ) ),
			HtmlTag::create( 'td', HtmlTag::create( 'span', $messages, ['class' => 'badge '.( $messages ? 'badge-success' : '' )] ) ),
			HtmlTag::create( 'td', $buttons ),
		) );
	}
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( array(
		'Quelle',
		'Ziel',
		'Zustand',
		'<i class="fa fa-fw fa-repeat" title="Durchläufe"></i>',
		'<i class="fa fa-fw fa-envelope" title="Nachrichten übertragen"></i>',
		'',
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$colgroup	= HtmlElements::ColumnGroup( '30%', '30%', '20%', '5%', '5%', '10%' );
	$table	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-fixed'] );
}
$panelSyncs	= '<div class="content-panel">
	<h3>Synchronisierungen</h3>
	<div class="content-panel-inner">
		<tbody>
			'.$table.'
		</tbody>
		<div class="buttonbar">
			<a href="./work/mail/sync/addSync" class="btn btn-success"><i class="fa fa-fw fa-plus"></i>&nbsp;neue Synchronisierung</a>
		</div>
	</div>
</div>';

return '<div class="row-fluid">
	<div class="span8">
		'.$panelSyncs.'
	</div>
	<div class="span4">
		'.$panelHosts.'
	</div>
</div>
<style>
div.progress {
	margin: 0;
	display: inline-block;
	width: 100%;
	}
</style>';
