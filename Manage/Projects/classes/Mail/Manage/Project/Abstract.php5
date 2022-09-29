<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

abstract class Mail_Manage_Project_Abstract extends Mail_Abstract
{
	protected function generate(): self
	{
//		$this->addThemeStyle( $modules->has( 'UI_Bootstrap' ) ? 'bootstrap.min.css' : 'mail.min.css' );
/*		if( $this->env->getModules()->has( 'UI_CSS_Panel' ) ){
			$options	= $this->env->getConfig()->getAll( 'module.ui_css_panel.', TRUE );
			if( $options->get( 'enabled' ) ){
				$this->page->addBodyClass( 'content-panel-style-'.$options->get( 'style' ) );
				$this->addCommonStyle( 'layout.panels.css' );
			}
		}*/
		return $this;
	}

	protected function collectFacts( $project ): View_Helper_Mail_Facts
	{
		$view		= new View( $this->env );
		$logic		= Logic_Project::getInstance( $this->env );
		$words		= $this->getWords( 'manage/project' );
		$members	= $logic->getProjectUsers( $project->projectId );

		$helperFacts	= new View_Helper_Mail_Facts();
		$helperFacts->setLabels( array_merge( $words['edit'], $words['mails'] ) );

		//  --  FACTS: TITLE, DESCRIPTION & LINK  --  //
		$helperFacts->add( 'title', $project->title );
		$helperFacts->add( 'description', $view->renderContent( $project->description ), $project->description );
		if( $project->url )
			$helperFacts->add( 'url', HtmlTag::create( 'a', $project->url, array(
				'href'		=> $project->url,
			) ) );

		//  --  FACTS: MEMBERS  --  //
		$list	= [];
		foreach( $members as $nr => $member ){
			$list[]	= $member->username;
			$members[$nr] = HtmlTag::create( 'li', $member->username );
		}
		$helperFacts->add( 'members', HtmlTag::create( 'ul', $members ), join( ", ", $list ) );

		//  --  FACTS: DATES & TIMES  --  //
		$time		= date( 'H:i:s', $project->createdAt );
		$date		= date( 'd.m.Y', $project->createdAt );
		$smallTime	= HtmlTag::create( 'small', '('.$time.')', ['class' => 'muted'] );
		$helperFacts->add( 'createdAt', $date.' '.$smallTime );
		if( $project->modifiedAt ){
			$time		= date( 'H:i:s', $project->modifiedAt );
			$date		= date( 'd.m.Y', $project->modifiedAt );
			$smallTime	= HtmlTag::create( 'small', '('.$time.')', ['class' => 'muted'] );
			$helperFacts->add( 'modifiedAt', $date.' '.$smallTime );
		}

		//  --  FACTS: PRIORITY & STATUS  --  //
		$smallPriority	= HtmlTag::create( 'small', '('.$project->priority.')', ['class' => 'muted'] );
		$labelPriority	= $words['priorities'][$project->priority];
		$helperFacts->add( 'priority', $labelPriority.' '.$smallPriority );

		$direction		= NULL;
		$direction		= in_array( $project->status, [3] ) ? 1 : $direction;
		$direction		= $project->status < 0 ? -1 : $direction;
		$smallStatus	= HtmlTag::create( 'small', '('.$project->status.')', ['class' => 'muted'] );
		$labelStatus	= $words['states'][$project->status];
		$textStatus		= $labelStatus.' ('.$project->status.')';
		$helperFacts->add( 'status', $labelStatus.' '.$smallStatus, NULL, $direction );

		return $helperFacts;
	}
}
