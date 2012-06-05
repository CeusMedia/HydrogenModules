<?php
$wf		= (object) $words['tab-general-authors'];

$list	= '<span class="hint">'.$wf->listNone.'</span>';
if( $module->authors ){
	$list	= array();
	foreach( $module->authors as $author ){
		$label	= $author->name;
		if( $author->email )
			$label	= UI_HTML_Tag::create( 'a', $label, array( 'href' => 'mailto:'.$author->email ) );
		$url	= './manage/module/editor/removeAuthor/'.$moduleId.'/'.base64_encode( $author->name );
		$button	= UI_HTML_Elements::LinkButton( $url, '', 'button icon tiny remove', $wf->buttonRemoveConfirm );
		$list[]	= '<li class="author">'.$label.'<div style="float: right">'.$button.'</div></li>';
	}
	$list	= '<ul class="general-info">'.join( $list ).'</ul>';
}

$buttonOpen		= '<button type="button" class="button iconed tiny add form-trigger"><span></span></button>';
$hideForMore	= ' style="display: none"';

$panelAuthors	= '
<form action="./manage/module/editor/addAuthor/'.$module->id.'" method="post">
	<fieldset>
		<legend class="icon author">'.$wf->legend.'</legend>
		'.$list.'
		<ul class="input"'.$hideForMore.'>
			<li>
				<label for="input_name" class="mandatory">'.$wf->labelName.'</label><br/>
				<input type="text" name="name" id="input_name" class="max mandatory" value=""/>
			</li>
			<li>
				<label for="input_email">'.$wf->labelEmail.'</label><br/>
				<input type="text" name="email" id="input_email" class="max" value=""/>
			</li>
		</ul>
		<div class="buttonbar"'.$hideForMore.'>
			'.UI_HTML_Elements::Button( 'addAuthor', $wf->buttonAdd, 'button add' ).'
		</div>
		'.$buttonOpen.'
	</fieldset>
</form>
';
return $panelAuthors;
?>