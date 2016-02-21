<div class="header-menu fixed">
    <button class="btn btn-default" id="shoppingModeToggle" data-toggle="button">Mode Courses</button>
    <button class="btn btn-default menu pull-right"><i class="fa fa-reorder"></i></button>
</div>
<div class="row content">
    <div class="col-md-3 shopping-mode-hide">
        <div id="menu">
            <h4>Listes</h4>
            <ul class="nav nav-pills nav-stacked" id="menu_lists">
            </ul>
            <h4>Actions</h4>
            <ul class="nav nav-pills nav-stacked" id="menu_actions">
                <li><a href="0"><i class="fa fa-plus fa-fw"></i> Créer une liste</a></li>
            </ul>
        </div>
    </div>
    <div class="col-md-9" id="defaultColumn">
        <p class="alert alert-info">Choisissez une liste dans le menu</p>
    </div>
    <div class="col-md-9" id="listColumn">
        <h4 class="shopping-mode-hide"><i class="fa fa-list fa-fw" id="list_icon"></i> <input type="text" placeholder="Titre de la liste" id="list_title" /></h4>
        <div class="well well-sm" id="list">
            <ul id="list_items">
                
            </ul>
            <div id="addItemContainer" class="shopping-mode-hide">
                <a href="#" id="item_add"><i class="fa fa-plus fa-fw"></i> Ajouter</a>
                <button id="list_save" class="btn btn-primary btn-xs pull-right"><i class="fa fa-save fa-fw"></i> Enregistrer</a>
            </div>
        </div>
        <h4 class="shopping-mode-hide">Options</h4>
        <div class="well well-sm shopping-mode-hide" id="list_options">
            <div class="form-vertical">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Groupe</label>
                            <div class="controls">
                                <select class="form-control" id="list_group"></select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Actions</label>
                            <div class="controls">
                                <div class="btn-group btn-group-justified">
                                    <a class="btn btn-danger" href="#" id="list_delete">Supprimer</a>
                                    <a class="btn btn-default" href="#" id="list_clear">Enlever cochés</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Création</label>
                            <div class="controls">
                                <div class="form-control" id="list_creation_date"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Dernière modification</label>
                            <div class="controls">
                                <div class="form-control" id="list_modification_date"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-icons">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Modifier l'icône</h4>
            </div>
            <div class="modal-body">
                <ul class="icons">
                    <?php
                    $icons = Array("bullhorn", "shopping-cart", "music", "file-text-o", "anchor", "archive", "arrows", "asterisk", "ban", "bar-chart-o", "barcode", "beer", "bell-o", "bolt", "book", "briefcase", "bug", "building-o", "calendar", "camera", "clock-o", "cloud", "coffee", "compass", "credit-card", "cutlery", "dashboard", "desktop", "envelope-o", "film", "flag", "flask", "gamepad", "gavel", "gift", "glass", "globe", "headphones", "heart", "home", "key", "laptop", "leaf", "lightbulb-o", "magic", "magnet", "microphone", "moon-o", "pencil", "phone", "plane", "print", "puzzle-piece", "road", "rocket", "suitcase", "tags", "tint", "trophy", "truck", "umbrella", "video-camera", "wrench", "bicycle", "bus", "calculator", "soccer-ball-o", "paint-brush", "newspaper-o", "bed", "diamond", "heartbeat", "motorcycle", "ship", "street-view", "subway", "train", "user-secret");
                    sort($icons);
                    foreach($icons as $icon) {
                        echo "<li><button class=\"btn btn-default\" data-value=\"$icon\"><i class=\"fa fa-$icon fa-fw\"></i></button></li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="assets/js/shopping.js"></script>
