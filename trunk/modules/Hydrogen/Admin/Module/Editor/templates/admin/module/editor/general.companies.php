<?php
$wf		= (object) $words['tab-general-companies'];

$list	= '<span class="hint">'.$wf->listNone.'</span>';
if( $module->companies ){
	$list	= array();
	foreach( $module->companies as $company ){
		$label	= $company->name;
		if( $company->site )
			$label	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $company->site ) );
		$url	= './manage/module/editor/removeCompany/'.$moduleId.'/'.base64_encode( $company->name );
		$button	= UI_HTML_Elements::LinkButton( $url, '', 'button icon tiny remove', $wf->buttonRemoveConfirm );
		$list[]	= '<li class="company">'.$label.'<div style="float: right">'.$button.'</div></li>';
	}
	$list	= '<ul class="general-info">'.join( $list ).'</ul>';
}

$buttonOpen		= '<button type="button" class="button iconed tiny add form-trigger"><span></span></button>';
$hideForMore	= ' style="display: none"';

$panelCompanies	= '
<form action="./manage/module/editor/addCompany/'.$module->id.'" method="post">
	<fieldset>
		<legend class="icon company">'.$wf->legend.'</legend>
		'.$list.'
		<ul class="input"'.$hideForMore.'>
			<li>
				<label for="input_name" class="mandatory">'.$wf->labelName.'</label><br/>
				<input type="text" name="name" id="input_name" class="max mandatory" value=""/>
			</li>
			<li>
				<label for="input_site">'.$wf->labelSite.'</label><br/>
				<input type="text" name="site" id="input_site" class="max" value=""/>
			</li>
		</ul>
		<div class="buttonbar"'.$hideForMore.'>
			'.UI_HTML_Elements::Button( 'addCompany', $wf->buttonAdd, 'button add' ).'
		</div>
		'.$buttonOpen.'
	</fieldset>
</form>
';
return $panelCompanies;
?>