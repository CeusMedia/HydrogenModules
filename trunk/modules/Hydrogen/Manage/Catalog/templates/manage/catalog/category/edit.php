<?php
$w	= (object) $words['edit'];

$listMain	= $this->renderTree( $categories, $category->categoryId );
$tabs		= $this->renderMainTabs();

$optParent	= array( 0 => '' );
foreach( $categories as $item )
	if( $item->parentId == 0 )
		$optParent[$item->categoryId]	= $item->label_de;
natcasesort( $optParent );
$optParent	= UI_HTML_Elements::Options( $optParent, (int) $category->parentId );

$optVisible	= $words['visible'];
$optVisible	= UI_HTML_Elements::Options( $optVisible, (int) $category->visible );

$disableParent	= $category->parentId == 0 ? 'disabled="disabled"' : "";
$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' '.$w->buttonCancel, array(
	'href'	=> "./manage/catalog/category",
	'class'	=> 'btn btn-small'
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.' '.$w->buttonSave, array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-small btn-success'
) );
$buttonRemove	= UI_HTML_Tag::create( 'button', $iconRemove.' '.$w->buttonRemove, array(
	'disabled'	=> $nrArticles ? 'disabled' : NULL,
	'type'		=> 'button',
	'class'		=> "btn btn-small btn-danger",
	'onclick'	=> "document.location.href='./manage/catalog/category/remove/".$category->categoryId."';"
) );

/*  --  ARTICLES IN CATEGORY  --  */
$listArticles	= '<em class="muted">Keine vorhanden.</em>';
if( $articles ){
	$rows	= array();
	foreach( $articles as $article ){
		$url	= './manage/catalog/article/edit/'.$article->articleId;
		$link	= UI_HTML_Tag::create( 'a', $article->title, array( 'href' => $url, 'title' => $article->volume ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $article->volume, array( 'style' => 'text-align: right' ) ),
		) );
	}
	$heads		= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Veröffentlichung' ),
		UI_HTML_Tag::create( 'th', 'Band', array( 'style' => 'text-align: right' ) ),
	) ) );
	$tbody			= UI_HTML_Tag::create( 'tbody', $rows );
	$listArticles	= UI_HTML_Tag::create( 'table', $heads.$tbody, array( 'class' => 'table table-striped table-small' ) );
}

return '
'.$tabs.'
<div class="row-fluid">
	<div class="span6">
		'.$listMain.'
	</div>
	<div class="span6">
		<h3>'.$w->heading.'</h3>
		<form action="manage/catalog/category/edit/'.$category->categoryId.'" method="post">
			<label for="input_parentId">'.$w->labelParentId.'</label>
			<select '.$disableParent.' class="span12" name="parentId" id="input_parentId">'.$optParent.'</select>
			<label for="input_label_de">'.$w->labelLabel.'</label>
			<input class="span12" type="text" name="label_de" id="input_label_de" value="'.htmlentities( $category->label_de, ENT_QUOTES, 'UTF-8' ).'"/>
			<label for="input_label_former">'.$w->labelLabelFormer.'</label>
			<input class="span12" type="text" name="label_former" id="input_label_former" value="'.htmlentities( $category->label_former, ENT_QUOTES, 'UTF-8' ).'"/>
			<label for="input_publisher">'.$w->labelPublisher.'</label>
			<input class="span12" type="text" name="publisher" id="input_publisher" value="'.htmlentities( $category->publisher, ENT_QUOTES, 'UTF-8' ).'"/>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_issn">'.$w->labelIssn.'</label>
					<input class="span12" type="text" name="issn" id="input_issn" value="'.htmlentities( $category->issn, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span5">
					<label for="input_visible">'.$w->labelVisible.'</label>
					<select class="span12" name="visible" id="input_visible">'.$optVisible.'</select>
				</div>
				<div class="span1">
					<label for="input_rank">'.$w->labelRank.'</label>
					<input class="span12" type="text" name="rank" id="input_rank" value="'.htmlentities( $category->rank, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
				'.$buttonRemove.'
			</div>
		</form>
		<hr/>
		<h4>'.$w->headingArticles.'</h4>
		'.$listArticles.'
	</div>
</div>
';
?>
