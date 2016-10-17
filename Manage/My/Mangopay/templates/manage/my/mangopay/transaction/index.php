<?php

return '<div class="row-fluid">
    <div class="span3">
        <div class="content-panel content-panel-filter">
            <h3>Filter</h3>
            <div class="content-panel-inner">
                <form action="./manage/my/mangopay/transaction/filter" method="post">
                    <div class="row-fluid">
                        <div class="span12">
                            <label for="input_nature">Nature</label>
                            <select name="nature" id="input_nature">
                                <option value="REGULAR">Regular</option>
                                <option value="REPUDIATION">Repudiation</option>
                                <option value="REFUND">Refund</option>
                                <option value="SETTLEMENT">Settlement</option>
                            </select>
                        </div>
                    </div>
                    <div class="buttonbar">
                        <button type=="submit" name="save" class="btn btn-primary not-btn-small">filter</button>
                        <a href="./manage/my/mangopay/transaction/filter/reset" class="btn btn-inverse btn-small">clear</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="span9">
        '.View_Helper_Panel_Mangopay_Transactions::renderStatic( $env, $transactions ).'
    </div>
</div>';

?>
