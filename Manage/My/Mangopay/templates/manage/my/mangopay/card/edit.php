<?php

$linkBack	= './'.( $backwardTo ? $backwardTo : 'manage/my/mangopay/card' );

$buttonDeactivate	= '<a href="./manage/my/mangopay/card/deactivate/'.$cardId.'" class="btn btn-small btn-danger"><b class="fa fa-remove"></b> entfernen</a>';

$data = print_m( $card, NULL, NULL, TRUE );

return '
<div class="row-fluid">
    <div class="span6">
        <div class="content-panel">
            <h3><i class="fa fa-fw fa-credit-card"></i> Kreditkarte bearbeiten</h3>
            <div class="content-panel-inner">
                <form action="./manage/my/mangopay/card/edit/'.$cardId.'" method="post">
                    <input type="hidden" name="backwardTo" value="'.$backwardTo.'"/>
                    <div class="row-fluid">
                        <div class="span3">
                            <label for="input_cardType">Card Type</label>
                            <input type="text" name="cardType" id="input_cardType" class="span12" readonly="readonly" value="'.$card->CardType.'"/>
                        </div>
                        <div class="span9">
                            <label for="input_title">Bezeichnung <small class="muted">(z.B. "Meine VISA-Karte")</small></label>
                            <input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $card->Tag, ENT_QUOTES, 'UTF-8' ).'"/>
                        </div>
                    </div>
                    <div class="buttonbar">
                        <a href="'.$linkBack.'" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>
                        <button type="submit" name="save" value="select" class="btn btn-primary"><b class="fa fa-check"></b> speichern</button>
					&nbsp;|&nbsp;
					'.$buttonDeactivate.'
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="span6">
        <div class="content-panel">
            <h3>Fakten</h3>
            <div class="content-panel-inner">
                <dl class="dl-horizontal">
                    <dt><abbr title="anonymisiert">Nummer</abbr></dt>
                    <dd>'.View_Helper_Panel_Mangopay::renderCardNumber( $card->Alias ).'</dd>
                    <dt>aktiviert</dt>
                    <dd>'.( $card->Active ? 'ja' : 'nein' ).'</dd>
                    <dt>Währung</dt>
                    <dd>'.$card->Currency.'</dd>
                    <dt>Anbieter</dt>
                    <dd>'.$card->CardProvider.'</dd>
                    <dt>Kartentyp</dt>
                    <dd>'.$card->CardType.'</dd>
                    <dt>erstellt am</dt>
                    <dd>'.date( 'd.m.Y', (int) $card->CreationDate ).'</dd>
                    <dt>gültig bis</dt>
                    <dd>'.substr( $card->ExpirationDate, 0, 2 ).'/20'.substr( $card->ExpirationDate, 2 ).'</dd>
                </dl>
                <hr/>
                '.$data.'
            </div>
        </div>
    </div>
</div>';
?>
