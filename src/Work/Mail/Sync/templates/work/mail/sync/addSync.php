<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

/** @var array<string,array<string,string>> $words */

$w	= (object) $words['addSync'];

/** @var array<object> $hosts */

$hostMap	= [];
foreach( $hosts as $host )
	$hostMap[$host->mailSyncHostId]	= $host->host ?: $host->ip;

$optHost1	= HtmlElements::Options( $hostMap );
$optHost2	= HtmlElements::Options( $hostMap );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./work/mail/sync/addSync" method="post">
					<div class="row-fluid">
						<div class="span4">
							<label for="input_sourceUsername">'.$w->labelSourceUsername.'</label>
							<input type="text" name="sourceUsername" id="input_sourceUsername" class="span12"/>
						</div>
						<div class="span2">
							<br/>
							<label><input type="checkbox" name="sameUsername" id="input_sameUsername" checked="checked"/> '.$w->labelSameUsername.'</label>
						</div>
						<div class="span4">
							<label for="input_targetUsername">'.$w->labelTargetUsername.'</label>
							<input type="text" name="targetUsername" id="input_targetUsername" readonly="readonly" class="span12"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_sourcePassword">'.$w->labelSourcePassword.'</label>
							<input type="text" name="sourcePassword" id="input_sourcePassword" class="span12"/>
						</div>
						<div class="span2">
							<br/>
							<label><input type="checkbox" name="samePassword" id="input_samePassword" checked="checked"/> '.$w->labelSamePassword.'</label>
						</div>
						<div class="span4">
							<label for="input_targetPassword">'.$w->labelTargetPassword.'</label>
							<input type="text" name="targetPassword" id="input_targetPassword" readonly="readonly" class="span12"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_sourceMailHostId">'.$w->labelSourceHost.'</label>
							<select name="sourceMailHostId" id="input_sourceMailHostId" class="span12">'.$optHost1.'</select>
						</div>
						<div class="span2">
						</div>
						<div class="span4">
							<label for="input_targetMailHostId">'.$w->labelTargetHost.'</label>
							<select name="targetMailHostId" id="input_targetMailHostId" class="span12">'.$optHost2.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label class="checkbox"><input type="checkbox" name="resync" value="1" checked="checked"/>&nbsp;'.$w->labelResync.'</label>
						</div>
					</div>
					<div class="buttonbar">
						<a href="./work/mail/sync" class="btn"><i class="fa fa-fw fa-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</a>
						<button type="submit" name="save" class="btn btn-primary" id="button_save"><i class="fa fa-fw fa-check"></i>&nbsp;'.$w->buttonSave.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
