<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconProcess  = HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$optProcess = [
  'turn'  => 'drehen',
  'flip'  => 'spiegeln',
];
$optProcess = HtmlElements::Options( $optProcess );

$optTurnDegree  = [
  '90'  => '90°',
  '180'  => '180°',
  '270'  => '270°',
];
$optTurnDegree = HtmlElements::Options( $optTurnDegree );

$optTurnDirection  = [
  '1'   => 'nach rechts',
  '-1'  => 'nach links',
];
$optTurnDirection = HtmlElements::Options( $optTurnDirection );

$optFlipDirection = array(
  '1'  => 'horizontal (an Y-Achse)',
  '0'  => 'vertikal (an X-Achse)',
);
$optFlipDirection = HtmlElements::Options( $optFlipDirection );

return '
<div class="content-panel">
  <h3>Drehen oder spiegeln</h3>
  <div class="content-panel-inner">
    <form action="./manage/content/image/process/'.base64_encode( $imagePath ).'" method="post">
      <div class="row-fluid">
        <div class="span4">
          <label for="input_process">Prozess</label>
          <select name="process" id="input_process" class="span12 has-optionals">'.$optProcess.'</select>
        </div>
        <div class="span3 optional process process-turn">
          <label for="input_turnDegree">Grad</label>
          <select name="turnDegree" id="input_turnDegree" class="span12">'.$optTurnDegree.'</select>
        </div>
        <div class="span5 optional process process-turn">
          <label for="input_turnDirection">Richtung</label>
          <select name="turnDirection" id="input_turnDirection" class="span12">'.$optTurnDirection.'</select>
        </div>
        <div class="span8 optional process process-flip" style="display: none">
          <label for="input_flipDirection">Richtung</label>
          <select name="flipDirection" id="input_flipDirection" class="span12">'.$optFlipDirection.'</select>
        </div>
      </div>
      <div class="buttonbar">
        <button type="submit" name="save" class="btn btn-primary">'.$iconProcess.'&nbsp;anwenden</button>
      </div>
    </form>
  </div>
</div>';
