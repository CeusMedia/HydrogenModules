<?php

use CeusMedia\HydrogenFramework\View;

class View_Admin_Mail_Queue extends View
{
	public function ajaxRenderDashboardPanel()
	{
		$model			= new Model_Mail( $this->env );

		$statuses		= array(
			Model_Mail::STATUS_SENT		=> 'versendet',
			Model_Mail::STATUS_FAILED	=> 'fehlgeschlagen',
			Model_Mail::STATUS_ABORTED	=> 'gescheitert',
		);
		$ranges		= array(
			1		=> 'Tag',
			7		=> 'Woche',
			30		=> 'Monat',
			365		=> 'Jahr',
		);
		$data	= [];
		foreach( $statuses as $statusKey => $statusLabel ){
			$data[$statusKey]	= [];
			foreach( $ranges as $rangeKey => $rangeLabel ){
				$conditions	= array(
					'status'		=> $statusKey,
					'enqueuedAt'	=> '>= '.( time() - $rangeKey * 24 * 3600 ),
				);
				$data[$statusKey][$rangeKey]	= $model->count( $conditions );
			}
		}

		$tableHeads		= array( '' );
		foreach( $ranges as $rangeLabel )
			$tableHeads[]	= UI_HTML_Tag::create( 'small', $rangeLabel, array( 'class' => 'pull-right' ) );

		$lastRangeLength	= @array_pop( array_keys( $ranges ) );

		$rows	= [];
		foreach( $statuses as $statusKey => $statusLabel ){
			$row	= [];
			foreach( array_reverse( $ranges, TRUE ) as $days => $label ){
				$lastRange	= (object) array(
					'key'		=> $days,
					'value'		=> $data[$statusKey][$days],
					'label'		=> $label,
				);
				break;
			}
			foreach( array_reverse( $ranges, TRUE ) as $rangeKey => $rangeLabel ){
				$label	= $data[$statusKey][$rangeKey];
				if( $rangeKey !== $lastRange->key && $data[$statusKey][$lastRange->key] > 10 ){
					$average	= $lastRange->value ? $data[$statusKey][$lastRange->key] / $lastRange->key : 0;
					$capacity	= $data[$statusKey][$rangeKey] / $rangeKey;
					$change		= $average ? round( ( ( $capacity / $average ) - 1 ) * 100, 0 ) : 0;
					$diff		= $change > 0 ? '+'.$change : $change;
					$label		.= '&nbsp;<small class="muted">'.$diff.'</small>';
				}
				$row[]	= UI_HTML_Tag::create( 'td', $label, array( 'style' => 'text-align: right' ) );
			}
			$row[]	= UI_HTML_Tag::create( 'th', $statusLabel );
			$rows[]	= UI_HTML_Tag::create( 'tr', array_reverse( $row ) );
		}
		$table2	= UI_HTML_Tag::create( 'table', array(
			UI_HTML_Elements::ColumnGroup( '', '15%', '15%', '15%', '15%' ),
			UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( $tableHeads ) ),
			UI_HTML_Tag::create( 'tbody', $rows ),
		), array(
			'class'		=> 'table table-condensed table-fixed',
		) );
		$table1	= UI_HTML_Tag::create( 'table', array(
			UI_HTML_Elements::ColumnGroup( '20%', '80%' ),
			UI_HTML_Tag::create( 'tbody', UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', '<span style="font-size: 3em">'.$model->count( array( 'status' => 0 ) ).'</span>', array( 'style' => 'text-align: right; vertical-align: bottom' ) ),
				UI_HTML_Tag::create( 'td', '<span>Mails in der<br/>Warteschlange</span>', array( 'style' => 'vertical-align: bottom' ) ),
			) ) ),
		), array(
			'class'		=> 'table table-fixed',
		) );
		return $table1.'<br/>'.$table2;
	}

	public function enqueue()
	{
	}

	public function html()
	{
		try{
			$mail	= $this->getData( 'mail' );
			$helper	= new View_Helper_Mail_View_HTML( $this->env );
			$helper->setMailObjectInstance( $mail->object->instance );
			print( $helper->render() );
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );
		}
		exit;
	}

	public function index()
	{
		$script	= 'ModuleAdminMail.Queue.init();';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	public function view()
	{
	}

	public function renderFact( $key, $value )
	{
		$words	= $this->env->getLanguage()->getWords( 'admin/mail/queue' );
		if( in_array( $key, array( 'object' ) ) )
			return;
		if( $key === 'status' ){
			$value = $words['states'][$value].' <small class="muted">('.$value.')</small>';
		}
		else if( $key === 'mailClass' ){
			$original	= $value;
			$value	= preg_replace( '/^Mail_/', '', $value );
			$value	= preg_replace( '/_/', ':', $value );
			$value	= UI_HTML_Tag::create( 'abbr', $value, array( 'title' => $original ) );
		}
		else if( preg_match( '/At$/', $key ) ){
			if( !( (int) $value ) )
				return;
			$helper	= new View_Helper_TimePhraser( $this->env );
			$date	= date( 'Y-m-d H:i:s', $value );
			$phrase	= $helper->convert( $value, TRUE, 'vor ' );
			$value	= $phrase.'&nbsp;<small class="muted">('.$date.')</small>';
		}
		else if( preg_match( '/Id$/', $key ) ){
			if( (int) $value === 0 )
				return;
		}
		else if( preg_match( '/Address/', $key ) && strlen( $value ) ){
			$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-envelope' ) );
			$link	= UI_HTML_Tag::create( 'a', $value, array( 'href' => 'mailto:'.$value ) );
			$value	= $icon.'&nbsp;'.$link;
		}
		else if( $key === "status" ){
			$value = $words['states'][$value].' <small class="muted">('.$value.')</small>';
		}
		else{
			if( !strlen( $value ) )
				return;
		}
		$label	= $words['view-facts']['label'.ucfirst( $key )];
		$term	= UI_HTML_Tag::create( 'dt', $label );
		$def	= UI_HTML_Tag::create( 'dd', $value.'&nbsp;' );
		return $term.$def;
	}

	protected function __onInit()
	{
		$this->env->getPage()->addCommonStyle( 'module.admin.mail.css' );
		$this->env->getPage()->js->addModuleFile( 'module.admin.mail.js' );
	}
}
