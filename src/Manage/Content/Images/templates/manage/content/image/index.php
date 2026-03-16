<?php
/** @var View_Manage_Content_Image $view */
/** @var bool $canAddFolder */
/** @var bool $canAddImage */
/** @var bool $canEditFolder */
/** @var bool $canEditImage */
/** @var bool $canProcess */
/** @var bool $canRemoveFolder */
/** @var bool $canRemoveImage */
/** @var bool $canScale */

$panelList		= $view->loadTemplateFile( 'manage/content/image/folders.php' );
$panelFolder	= $view->loadTemplateFile( 'manage/content/image/index.folder.php' );

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/content/image/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelList.'
	</div>
	<div class="span9">
		'.$panelFolder.'
	</div>
</div>
'.$textBottom;
