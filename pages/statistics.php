<div class="header-menu fixed">
    <span class="title-pull">Statistiques</span>
    <button class="btn btn-default menu pull-right"><i class="fa fa-reorder"></i></button>
    <span class="pull-right">
        <?php
        $accounts = Array();
        foreach(User::getAuth()->getAccounts() as $account) {
            $accounts[$account->getId()] = $account->aName;
        }
        echo UI::select("account", $accounts, array_key_exists('id', $_GET) ? $_GET['id'] : 0, "account", true);
        ?>
    </span>
</div>
<div class="row">
    <div class="col-md-3">
        <h4>Séries</h4>
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon"><span style="display:inline-block;width:30px;">De</span></span>
                <input type="text" class="form-control bDate" />
            </div>
        </div>
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon"><span style="display:inline-block;width:30px;">À</span></span>
                <input type="text" class="form-control eDate" />
            </div>
        </div>
        <div class="form-group">
            <div class="btn-group btn-group-justified graphType" data-toggle="buttons">
                <label class="btn btn-default" title="Histogramme">
                    <input type="radio" name="graphType" value="bar" autocomplete="off"> <i class="fa fa-bar-chart"></i>
                </label>
                <label class="btn btn-default" title="Courbe">
                    <input type="radio" name="graphType" value="line" autocomplete="off"> <i class="fa fa-line-chart"></i>
                </label>
                <label class="btn btn-default" title="Courbe cumulée">
                    <input type="radio" name="graphType" value="area" autocomplete="off"> <i class="fa fa-area-chart"></i>
                </label>
            </div>
        </div>
        <div class="form-group text-right">
            <button class="btn btn-primary replot">Mettre à jour</button>
        </div>
        <h4>Légende</h4>
        <ul class="options-series"></ul>
    </div>
    <div class="col-md-9">
        <div id="diagramContainer">
            <canvas id="diagram" height="170"></canvas>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-bordered table-condensed" id="table">
                <thead></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript" src="assets/vendor/Chart.js/Chart.min.js"></script>
<script type="text/javascript" src="assets/js/statistics.js"></script>
