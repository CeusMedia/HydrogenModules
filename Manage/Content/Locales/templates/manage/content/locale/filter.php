<?php

$languageSelector	= '';
if( count( $languages ) > 1 ){
	$optFolder		= '';
	$optLanguage	= UI_HTML_Elements::Options( array_combine( $languages, $languages ), $language );
	if( $language && isset( $folders )){
		$optFolder	= array( '' );
		foreach( $folders as $folderKey => $folderPath )
			$optFolder[$folderKey]	= $folderKey;
		$optFolder		= UI_HTML_Elements::Options( $optFolder, $folder );
	}
	$filterLanguage	= '
	<div class="span5">
		<label for="input_language">'.$words['index']['labelLanguage'].'</label>
		<select name="language" id="input_language" class="max mandatory not-cmSelectBox span12" onchange="this.form.submit()">'.$optLanguage.'</select>
	</div>';
}
//print_m( $filterShow );die;
//$optEmpty	= $words['show-types'];
//$optEmpty	= UI_HTML_Elements::Options( $optEmpty, $filterEmpty );

$optFolder	= array();
foreach( $folders as $folderKey => $folderPath )
	$optFolder[$folderKey]	= $words['folders'][$folderKey];
$optFolder	= UI_HTML_Elements::Options( $optFolder, $folder );

$helper	= new View_Helper_Manage_Content_List( $env );
$helper->setFiles( $files );
$helper->setCurrent( $file );
$helper->setLanguage( $language );
$helper->setFolder( $folder );

$list	= $helper->render();

return '
<div class="content-panel">
	<h3>'.$words['index']['heading'].'</h3>
	<div class="content-panel-inner">
		<form name="form_content-selector" action="./manage/content/locale/filter" method="post">
			<div class="row-fluid">
				<div class="span7">
					<label for="input_folder">'.$words['index']['labelFolder'].'</label>
					<select name="folder" id="input_folder" class="span12" onchange="this.form.submit()">'.$optFolder.'</select>
				</div>
				'.$filterLanguage.'
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label class="checkbox">
						<input type="checkbox" name="empty" id="input_empty" onchange="this.form.submit()" value="1" '.( $filterEmpty ? 'checked="checked"' : '').'/>
						'.$words['index']['labelEmpty'].'
					</label>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					'.$list.'
				</div>
			</div>
		</form>
	</div>
</div>';
?>
