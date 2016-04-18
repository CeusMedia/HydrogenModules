<?php
//return '[uberlog::index]';

$add	= '<a href="./work/uberlog/testRecord">test</a> | <button id="testRecordAjax">test AJAX</button>

<script>
$(document).ready(function(){
	UberlogClient.host = "'.getEnv( 'HTTP_HOST' ).'";
	$("#testRecordAjax").bind("click",function(){
		UberlogClient.record({
			category: "test",
			message: "test record @ "+new Date().getTime()
		});
	});
});
</script>
';

$panelFilter	= '
<div class="content-panel">
	<h4>Filter</h4>
	<div class="content-panel-inner">
		<ul class="input">
			<li>
				<label for="" class=""></label><br/>
				<input type="text"/>
			</li>
		</ul>
		<div class="buttonbar">
		</div>
	</div>
</div>
';

		$list	= array();
		$lastId	= 0;

		foreach( $records as $record ){
			$lastId	= max( $lastId, $record->logRecordId );
			if( $record->userAgent ){
				if(is_object( $record->userAgent ) )
					$record->userAgent	= $record->userAgent->title;
				$parts = explode( ' ', $record->userAgent );
				$title = array_shift( $parts );
				$record->userAgent	= '<acronym title="'.join(' ', $parts ).'">'.$title.'</acronym>';
			}
			if( $record->category ){
				if( is_object( $record->category ) )
					$record->category	= $record->category->title;
			}
			if( $record->host ){
				if( is_object( $record->host ) )
					$record->host	= $record->host->title;
			}
			if( $record->client ){
				if( is_object( $record->client ) )
					$record->client	= $record->client->title;
			}

			$list[]	= '<tr id="record-'.$record->logRecordId.'" class="type'.$record->type.'">
				<td>'.$record->logRecordId.'</td>
				<td><div class="autocut"><small class="muted">'.$record->code.':</small> '.$record->message.'</div><small class="muted">'.$record->source.':'.$record->line.'</small></td>
				<td>'.$record->client.'<br/><small class="muted">'.$record->host.'</small></td>
				<td>
					'.$record->category.'<br/>
					<small class="muted">'.$record->type.'</small>
				</td>
				<td>
					'.date( 'j.n.Y', $record->timestamp ).'<br/>
					'.date( 'H:i:s', $record->timestamp ).'
				</td>
				<td><button type="button" onclick="WorkUberlogView.removeRecord('.$record->logRecordId.');" class="btn btn-small">X</button></td>
			</tr>';
		}
		$list	= '
<div class="content-panel">
	<h4>Einträge</h4>
	<div class="content-panel-inner">
		<table class="table tabe-striped">
			<thead>
				<tr>
					<th>Nr</th>
					<th>Message</th>
					<th>Client / Host</th>
					<th>Category / Type</th>
					<th>Date / Time</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				'.join( $list ).'
			</tbody>
		</table>
		<script>
		var lastId = '.$lastId.'
		</script>
			</div>
		</div>
	';
return '
<div class="row-fluid">
<!--	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">-->
	<div class="span12">
		'.$list.'
	</div>
</div>
'.$add;
?>
