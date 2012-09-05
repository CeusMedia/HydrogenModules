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
<fieldset>
	<legend>Filter</legend>
	<ul class="input">
		<li>
			<label for="" class=""></label><br/>
			<input type="text"/>
		</li>
	</ul>
	<div class="buttonbar">
	</div>
</fieldset>
';



		$list	= array();
		$lastId	= 0;
		
		foreach( $records as $record ){
			$lastId	= max( $lastId, $record->recordId );
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
			
			$list[]	= '<tr id="record-'.$record->recordId.'" class="type'.$record->type.'">
				<td>'.$record->message.'</td>
				<td>
					<acronym title="'.$record->recordId.'">#</acronym>
					<acronym title="'.$record->type.'">TY</acronym>
					<acronym title="'.date( 'Y-m-d', $record->timestamp ).'">DA</acronym>
					<acronym title="'.date( 'H:i:s', $record->timestamp ).'">TI</acronym>
					<acronym title="'.$record->source.':'.$record->line.'">SF</acronym>
					<acronym title="'.$record->client.'">CL</acronym>
					<acronym title="'.$record->host.'">HO</acronym>
					<acronym title="'.$record->category.'">CA</acronym>
					<acronym title="'.$record->code.'">CO</acronym>
					'.$record->userAgent.'
				</td>
				<td><button type="button" onclick="WorkUberlogView.removeRecord('.$record->recordId.');">X</button></td>
			</tr>';
		}
		$list	= '<table class="uberlog">
			<thead>
				<tr>
					<th>Message</th>
					<th>Information</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<tr><td colspan="8" style="background-color: #EEE"><em>'.date( 'Y-m-d H:i' ).'</em></td></tr>
				'.join( $list ).'
			</tbody>
		</table>
		<script>
		var lastId = '.$lastId.'
		</script>';
return '
<div class="column-left-20">
	'.$panelFilter.'
</div>
<div class="column-right-80">
<!--	<fieldset>
		<legend>Liste</legend>
-->		'.$list.'
<!--	</fieldset>-->
</div>
<div class="column-clear"></div>
'.$add;
?>