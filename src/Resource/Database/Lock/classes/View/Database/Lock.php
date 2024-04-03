<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Database_Lock extends View{

	public function ajaxRenderDashboardPanel(): string
	{
		$context	= new View_Helper_Work_Time_Timer( $this->env );
		$payload	= [];
		$this->env->getCaptain()->callHook( 'Work_Timer', 'registerModule', $context, $payload );
		$modules	= $context->getRegisteredModules();

		$locks		= $this->getData( 'locks' );
		$content	= HtmlTag::create( 'div', 'Keine vorhanden.', ['class' => 'alert alert-info'] );
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
				$link		= HtmlTag::create( 'a', $lock->relationTitle, [
					'href' => $lock->relationLink,
				] );
				$time		= ceil( ( time() - $lock->timestamp ) / 60 ) * 60;
				$time		= View_Helper_Work_Time::formatSeconds( $time);
				$username	= HtmlTag::create( 'small', $lock->user->username );
				$rows[]	= HtmlTag::create( 'tr', array(
					HtmlTag::create( 'td', $username.'<br/>'.$link, ['class' => 'autocut'] ),
					HtmlTag::create( 'td', $time, ['style' => 'text-align: right'] ),
				) );
			}
			$colgroup	= HtmlElements::ColumnGroup( ["", "100px"] );
			$tbody		= HtmlTag::create( 'tbody', $rows );
			$content	= HtmlTag::create( 'table', $colgroup.$tbody, ['class' => 'table table-fixed'] );
		}
		return $content;
	}

	public function index(): void
	{
		$this->setPageTitle();
	}
}
