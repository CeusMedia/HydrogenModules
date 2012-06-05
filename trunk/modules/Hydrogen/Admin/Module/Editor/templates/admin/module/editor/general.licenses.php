<?php
$wf		= (object) $words['tab-general-licenses'];

$list	= '<span class="hint">'.$wf->listNone.'</span>';
if( $module->licenses ){
	$list	= array();
	foreach( $module->licenses as $license ){
		$label	= $license->label;
		if( $license->source )
			$label	= UI_HTML_Tag::create( 'a', $label, array( 'href' => 'mailto:'.$license->source ) );
		$url	= './manage/module/editor/removeLicense/'.$moduleId.'/'.base64_encode( $license->label );
		$button	= UI_HTML_Elements::LinkButton( $url, '', 'button icon tiny remove', $wf->buttonRemoveConfirm );
		$list[]	= '<li class="author">'.$label.'<div style="float: right">'.$button.'</div></li>';
	}
	$list	= '<ul class="general-info">'.join( $list ).'</ul>';
}

$buttonOpen		= '<button type="button" class="button iconed tiny add form-trigger"><span></span></button>';
$hideForMore	= ' style="display: none"';

$panelAuthors	= '
<form action="./manage/module/editor/addLicense/'.$module->id.'" method="post">
	<fieldset>
		<legend>'.$wf->legend.'</legend>
		'.$list.'
		<ul class="input"'.$hideForMore.'>
			<li>
				<label for="input_label" class="mandatory">'.$wf->labelLabel.'</label><br/>
				<input type="text" name="label" id="input_label" class="max mandatory" value=""/>
			</li>
			<li>
				<label for="input_source">'.$wf->labelSource.'</label><br/>
				<input type="text" name="source" id="input_source" class="max" value=""/>
			</li>
		</ul>
		<div class="buttonbar"'.$hideForMore.'>
			'.UI_HTML_Elements::Button( 'addLicense', $wf->buttonAdd, 'button add' ).'
		</div>
		'.$buttonOpen.'
	</fieldset>
</form>
';
return $panelAuthors;
?>