<?php

use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Manage_Project_Removed extends Mail_Manage_Project_Abstract
{
	/**
	 *	@return		static
	 *	@throws		ReflectionException
	 *	@throws		IoException
	 */
	protected function generate(): static
	{
		parent::generate();
		$baseUrl	= $this->env->url;
		$config		= $this->env->getConfig();
		$words		= $this->getWords( 'manage/project' );
		$data		= $this->data;
		$w			= (object) $words['mail-removed'];
		$project	= $data['project'];

		$this->setSubject( sprintf( $w->subject, $project->title ) );

		$helperFacts	= $this->collectFacts( $project );

		//  --  RELATED ITEMS  --  //
		$relations			= '';
		$helperRelations	= new View_Helper_ItemRelationLister( $this->env );
		$helperRelations->setHook( 'Project', 'listRelations', ['projectId' => $project->projectId] );
		$helperRelations->setLinkable( FALSE );
		$helperRelations->setActiveOnly( FALSE );
		//$helperRelations->setTableClass( 'limited' );
		//$helperRelations->setMode( 'list' );
		if( $helperRelations->hasRelations() ){
			$relations	= $helperRelations->render();
			$relations  = HtmlTag::create( 'h4', $w->headingRelations ).$relations;
		}

		//  --  FORMAT: PLAIN TEXT  --  //
		$helperText	= new View_Helper_Mail_Text();
		$helperFacts->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT );
		$text	= $helperText->underscore( $config->get( 'app.name' ), '=' ).PHP_EOL.
			PHP_EOL.
			sprintf( $w->headingText, $project->title ).PHP_EOL.
			PHP_EOL.
			$helperText->underscore( $w->headingFacts ).PHP_EOL.
			$helperFacts->render();

		//  --  FORMAT: HTML  --  //
		$helperFacts->setFormat( View_Helper_Mail_Facts::FORMAT_HTML );
		$body	= '
<div class="alert alert-danger">'.sprintf( $w->headingHtml, $project->title ).'</div>
<div class="content-panel">
	<h3>'.sprintf( $w->headingHtml, $project->title ).'</h3>
	<div class="content-panel-inner">
		<h4>'.$w->headingFacts.'</h4>
		'.$helperFacts->render().'
		'.$relations.'
		</dl>
	</div>
</div>';
		return $this->setHtml( $body )->setText( $text );
	}
}
