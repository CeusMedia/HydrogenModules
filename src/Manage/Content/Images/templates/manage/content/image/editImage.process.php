<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['editImage.process'];

$iconProcess  = HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$optProcess = $words['editImage.process.processes'];
$optProcess = HtmlElements::Options( $optProcess );

$optTurnDegree  = [
  '90'  => '90°',
  '180'  => '180°',
  '270'  => '270°',
];
$optTurnDegree = HtmlElements::Options( $optTurnDegree );

$optTurnDirection  = $words['editImage.process.turnDirections'];
$optTurnDirection = HtmlElements::Options( $optTurnDirection );

$optFlipDirection = $words['editImage.process.flipDirections'];
$optFlipDirection = HtmlElements::Options( $optFlipDirection );

return '
<div class="content-panel">
  <h3>'.$w->heading.'</h3>
  <div class="content-panel-inner">
    <form action="./manage/content/image/process/'.base64_encode( $imagePath ).'" method="post">
      <div class="row-fluid">
        <div class="span4">
          <label for="input_process">'.$w->labelProcess.'</label>
          <select name="process" id="input_process" class="span12 has-optionals">'.$optProcess.'</select>
        </div>
        <div class="span3 optional process process-turn">
          <label for="input_turnDegree">'.$w->labelDegree.'</label>
          <select name="turnDegree" id="input_turnDegree" class="span12">'.$optTurnDegree.'</select>
        </div>
        <div class="span5 optional process process-turn">
          <label for="input_turnDirection">'.$w->labelDirection.'</label>
          <select name="turnDirection" id="input_turnDirection" class="span12">'.$optTurnDirection.'</select>
        </div>
        <div class="span8 optional process process-flip" style="display: none">
          <label for="input_flipDirection">'.$w->labelDirection.'</label>
          <select name="flipDirection" id="input_flipDirection" class="span12">'.$optFlipDirection.'</select>
        </div>
      </div>
      <div class="buttonbar">
        <button type="submit" name="save" class="btn btn-primary">'.$iconProcess.'&nbsp;'.$w->buttonSave.'</button>
      </div>
    </form>
  </div>
</div>';
