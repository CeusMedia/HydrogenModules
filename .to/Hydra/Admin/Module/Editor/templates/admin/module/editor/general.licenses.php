<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$wf		= (object) $words['tab-general-licenses'];

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconLicense	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-gavel'] );

$list	= '<div class="alert alert-info former-hint">'.$wf->listNone.'</div>';
if( $module->licenses ){
	$list	= [];
	foreach( $module->licenses as $license ){
		$label	= $license->label;
		if( $license->source )
			$label	= HtmlTag::create( 'a', $label, ['href' => 'mailto:'.$license->source] );
		$url	= './admin/module/editor/removeLicense/'.$moduleId.'/'.base64_encode( $license->label );
		$button	= HtmlElements::LinkButton( $url, '', 'button icon tiny remove', $wf->buttonRemoveConfirm );
		$list[]	= '<li class="author">'.$label.'<div style="float: right">'.$button.'</div></li>';
	}
	$list	= '<ul class="general-info">'.join( $list ).'</ul>';
}

$buttonOpen		= '<button type="button" class="btn btn-mini btn-success form-trigger former-button former-iconed former-tiny former-add">'.$iconAdd.'</button>';
$hideForMore	= ' style="display: none"';

$panelAuthors	= '
<form action="./admin/module/editor/addLicense/'.$module->id.'" method="post">
	<div>
		<h4>'.$iconLicense.'&nbsp;'.$wf->legend.'</h4>
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
			'.HtmlElements::Button( 'addLicense', $wf->buttonAdd, 'button add' ).'
		</div>
		'.$buttonOpen.'
	</div>
</form>';
return $panelAuthors;
