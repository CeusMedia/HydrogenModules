<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Database_Lock extends View{

	public function ajaxRenderDashboardPanel(){
		$context	= new View_Helper_Work_Time_Timer( $this->env );
		$this->env->getCaptain()->callHook( 'Work_Timer', 'registerModule', $context, array() );
		$modules	= $context->getRegisteredModules();

		$locks		= $this->getData( 'locks' );
		$content	= HtmlTag::create( 'div', 'Keine vorhanden.', array( 'class' => 'alert alert-info' ) );
		if( $locks ){
			$rows	= [];
			foreach( $locks as $lock ){

				$module	= $modules[$lock->subject];
				$entry	= $module->model->get( $lock->entryId );
				if( !$entry )
					throw new Exception( 'Relation between timer and module is invalid' );
				$lock->type				= $module->typeLabel;
				$lock->relation			= $entry;
				$lock->relationTitle	= $entry->{$module->column};
				$lock->relationLink		= str_replace( "{id}", $lock->entryId, $module->link );
				$link		= HtmlTag::create( 'a', $lock->relationTitle, array(
					'href' => $lock->relationLink,
				) );
				$time		= ceil( ( time() - $lock->timestamp ) / 60 ) * 60;
				$time		= View_Helper_Work_Time::formatSeconds( $time);
				$username	= HtmlTag::create( 'small', $lock->user->username );
				$rows[]	= HtmlTag::create( 'tr', array(
					HtmlTag::create( 'td', $username.'<br/>'.$link, array( 'class' => 'autocut' ) ),
					HtmlTag::create( 'td', $time, array( 'style' => 'text-align: right' ) ),
				) );
			}
			$colgroup	= UI_HTML_Elements::ColumnGroup( array( "", "100px" ) );
			$tbody		= HtmlTag::create( 'tbody', $rows );
			$content	= HtmlTag::create( 'table', $colgroup.$tbody, array( 'class' => 'table table-fixed' ) );
		}
		return $content;
	}

	public function index(){
		$this->setPageTitle();
	}
}
?>
