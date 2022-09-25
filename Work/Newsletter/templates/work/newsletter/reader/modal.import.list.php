<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) ).'&nbsp;';

$optStatus	= $words->states;
unset( $optStatus[-1] );
$optStatus	= UI_HTML_Elements::Options( $optStatus );

$w	= (object) $words->add;

$optGroupId	= [];
foreach( $groups as $group )
	$optGroupId[$group->newsletterGroupId]	= $group->title;
$optGroupId	= UI_HTML_Elements::Options( $optGroupId );

return '
<form action="./work/newsletter/reader/import/list" method="post">
	<div id="modalImportList" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">Adressliste importieren</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="span9">
					<label for="input_groupId">Gruppe</label>
					<select name="groupId" id="input_groupId" class="span12">'.$optGroupId.'</select>
				</div>
				<div class="span3">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_addresses">Addressliste</label>
					<textarea name="addresses" id="input_addresses" class="span12" rows="10" required="required"></textarea>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">'.$iconCancel.'abbrechen</button>
			<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'importieren</button>
		</div>
	</div>
</form>';
