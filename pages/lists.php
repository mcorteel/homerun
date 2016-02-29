<div class="header-menu fixed">
    <button class="btn btn-default" id="drawer_toggle"><i class="fa fa-caret-right"></i></button>
    <button class="btn btn-default" id="shoppingModeToggle" data-toggle="button">Mode Courses</button>
    <button class="btn btn-default menu pull-right"><i class="fa fa-reorder"></i></button>
</div>
<div class="row content">
    <div class="col-md-3 col-sm-4 shopping-mode-hide" id="drawer">
        <div id="menu">
            <h4>Listes</h4>
            <ul class="nav nav-pills nav-stacked" id="menu_lists">
                <li><a><i class="fa fa-spinner fa-pulse fa-fw"></i> Chargement</a></li>
            </ul>
            <h4>Actions</h4>
            <ul class="nav nav-pills nav-stacked" id="menu_actions">
                <li><a href="0"><i class="fa fa-plus fa-fw"></i> Créer une liste</a></li>
            </ul>
        </div>
    </div>
    <div class="col-md-9 col-sm-8" id="defaultColumn">
        <p class="alert alert-info">Choisissez une liste dans le menu</p>
    </div>
    <div class="col-md-9 col-sm-8" id="loadingColumn">
        <h4><i class="fa fa-spinner fa-pulse fa-fw"></i> Chargement</h4>
    </div>
    <div class="col-md-9 col-sm-8" id="listColumn">
        <h4 class="shopping-mode-hide"><i class="fa fa-list fa-fw" id="list_icon"></i> <input type="text" placeholder="Titre de la liste" id="list_title" /></h4>
        <div class="well well-sm" id="list">
            <ul id="list_items">
                
            </ul>
            <div id="addItemContainer" class="shopping-mode-hide">
                <a href="#" id="item_add"><i class="fa fa-plus fa-fw"></i> Ajouter</a>
                <button id="list_save" class="btn btn-primary btn-xs pull-right" disabled="disabled"><i class="fa fa-save fa-fw"></i> Enregistrer</a>
            </div>
        </div>
        <h4 class="shopping-mode-hide">Options</h4>
        <div class="well well-sm shopping-mode-hide" id="list_options">
            <div class="form-vertical">
                <div class="row">
                    <div class="col-sm-6">
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
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label">Création</label>
                            <div class="controls">
                                <div class="form-control" readonly id="list_creation_date"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Dernière modification</label>
                            <div class="controls">
                                <div class="form-control" readonly id="list_modification_date"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
echo UI::iconsModal();
?>
<script type="text/javascript" src="assets/js/lists.js"></script>
