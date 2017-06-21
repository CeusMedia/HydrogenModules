<?php
class View_Admin_Mail_Queue extends CMF_Hydrogen_View{

	public function __onInit(){
	}

	public function ajaxRenderDashboardPanel(){
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
		);
		$lastRange	= @array_pop( array_keys( $ranges ) );
		$data	= array();
		foreach( $statuses as $statusKey => $statusLabel ){
			$data[$statusKey]	= array();
			foreach( $ranges as $rangeKey => $rangeLabel ){
				$conditions	= array(
					'status'		=> $statusKey,
					'enqueuedAt'	=> '>='.( time() - $rangeKey * 24 * 3600 ),
				);
				$data[$statusKey][$rangeKey]	= $model->count( $conditions );
			}
		}

		$tableHeads		= array( '' );
		foreach( $ranges as $rangeLabel )
			$tableHeads[]	= $rangeLabel;

		$rows	= array();
		foreach( $statuses as $statusKey => $statusLabel ){
			$row	= array( UI_HTML_Tag::create( 'th', $statusLabel ) );
			foreach( $ranges as $rangeKey => $rangeLabel ){
				$label	= $data[$statusKey][$rangeKey];
				if( $rangeKey !== $lastRange && $data[$statusKey][$lastRange] ){
					$average	= $data[$statusKey][$lastRange] / $lastRange;
					$capacity	= $data[$statusKey][$rangeKey] /  $rangeKey;
					$change		= round( ( ( $capacity / $average ) - 1 ) * 100, 0 );
					$diff		= $change > 0 ? '+'.$change : $change;
					$label		.= '&nbsp;<small class="muted">'.$diff.'</small>';
				}
				$row[]	= UI_HTML_Tag::create( 'td', $label );
			}
			$rows[]	= UI_HTML_Tag::create( 'tr', $row );
		}
		$table2	= UI_HTML_Tag::create( 'table', array(
			UI_HTML_Elements::ColumnGroup( '', '20%', '20%', '20%' ),
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

	public function enqueue(){
	}

	public function html(){
		try{
			$mail	= $this->getData( 'mail' );
			$helper	= new View_Helper_Mail_View_HTML( $this->env );
			$helper->setMail( $mail );
			print( $helper->render() );
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );
		}
		exit;
	}

	public function index(){
	}

	public function view(){
	}

	public function renderFact( $key, $value ){
		$words	= $this->env->getLanguage()->getWords( 'admin/mail/queue' );
		if( in_array( $key, array( "object" ) ) )
			return;
		if( preg_match( "/At$/", $key ) ){
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
}
?>
