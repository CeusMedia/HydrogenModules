<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Database_Dashboard_Locks
{
	protected Environment $env;

	protected ?array $locks			= NULL;

	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function render(): string
	{
		if( NULL === $this->locks )
			throw new RuntimeException( 'No locks set' );
		if( [] === $this->locks )
			return HtmlTag::create( 'div', 'Keine vorhanden.', ['class' => 'alert alert-info'] );

		$context	= new View_Helper_Work_Time_Timer( $this->env );
		$payload	= [];
		$this->env->getCaptain()->callHook( 'Work_Timer', 'registerModule', $context, $payload );
		$modules	= $context->getRegisteredModules();

		$rows	= [];
		foreach( $this->locks as $lock ){
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
		return HtmlTag::create( 'table', $colgroup.$tbody, ['class' => 'table table-fixed'] );
	}

	/**
	 *	@param		array		$locks
	 *	@return		void
	 */
	public function setLocks( array $locks ): void
	{
		$this->locks	= $locks;
	}
}
