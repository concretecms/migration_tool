<?php defined('C5_EXECUTE') or die("Access Denied.");
$dh = Core::make('helper/date');
/* @var \Concrete\Core\Localization\Service\Date $dh */
?>
    <div class="ccm-dashboard-header-buttons">

        <?php if ($activeProcesses) { ?>
            <div class="btn-group btn-group">
                <a href="javascript:void(0)" class="btn btn-danger" data-dialog-width="500" data-dialog="reset-processes"
                   data-dialog-title="<?= t('Reset All Processes') ?>"><?= t("Reset All Processes") ?></a>
            </div>

        <?php } else {  ?>
            <div class="btn-group btn-group-sm" role="group" aria-label="...">
                <a href="javascript:void(0)" data-dialog="add-to-batch" data-dialog-title="<?= t('Add Content') ?>"
                   class="btn btn-light"><?= t("Add Content to Batch") ?></a>
                <a href="<?= $view->action('batch_files', $batch->getID()) ?>" class="btn btn-light"><?= t('Files') ?></a>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                        <?= t('Edit') ?>
                        <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="<?= $view->action('map_content', $batch->getId())?>"><?=t('Map Content')?></a>
                        <div class="dropdown-divider"></div>
                        <div class="dropdown-header"><?= t('Settings') ?></div>
                        <a class="dropdown-item" href="<?=URL::to('/dashboard/system/migration/import/settings/basics', $batch->getID())?>" class=""><?= t("Basics") ?></a>
                            <?php foreach($settings as $setting) { ?>
                        <a class="dropdown-item" href="<?=URL::to($setting, $batch->getID())?>" class=""><?= t($setting->getCollectionName())?></a>
                            <?php } ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="javascript:void(0)" data-dialog="clear-batch-mappings"
                               data-dialog-title="<?= t('Clear Batch Mappings') ?>" class=""><?= t("Clear Batch Mappings") ?></a>
                        <a class="dropdown-item" href="javascript:void(0)" data-action="rescan-batch"
                               data-dialog-title="<?= t('Rescan Batch') ?>" class=""><?= t("Rescan Batch") ?></a>
                    </div>
                </div>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                        <?= t('Delete') ?>
                        <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu">
                        <a class="disabled dropdown-item" href="javascript:void(0)" data-dialog="delete-batch-items"
                                                data-dialog-title="<?= t('Delete Selected') ?>"><?= t("Delete Selected") ?></a>
                        <div class="dropdown-divider"></div>
                        <a href="javascript:void(0)" class="dropdown-item" data-dialog="clear-batch"
                               data-dialog-title="<?= t('Clear Batch') ?>" class=""><?= t("Clear Batch") ?></a>
                        <a class="dropdown-item" href="javascript:void(0)" data-dialog="delete-batch"
                               data-dialog-title="<?= t('Delete Batch') ?>"><?= t("Delete Batch") ?></a>
                    </div>
                </div>

                <a href="javascript:void(0)" class="btn btn-primary" data-dialog="create-content"
                   data-dialog-title="<?= t('Import Batch to Site') ?>" class=""><?= t("Import Batch to Site") ?></a>

            </div>

        <?php } ?>
    </div>

    <div style="display: none">

        <div data-dialog-wrapper="delete-batch"">
        <div id="ccm-dialog-delete-batch" class="ccm-ui">
            <form method="post" action="<?= $view->action('delete_batch') ?>">
                <?= Loader::helper("validation/token")->output('delete_batch') ?>
                <input type="hidden" name="id" value="<?= $batch->getID() ?>">
                <p><?= t('Are you sure you want to delete this import batch? This cannot be undone.') ?></p>
                <div class="dialog-buttons">
                    <button class="btn btn-light float-left"
                            onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
                    <button class="btn btn-danger float-right"
                            onclick="$('#ccm-dialog-delete-batch form').submit()"><?= t('Delete Batch') ?></button>
                </div>
            </form>
        </div>
        </div>


        <div data-dialog-wrapper="clear-batch">
        <div id="ccm-dialog-clear-batch" class="ccm-ui">
            <form method="post" action="<?= $view->action('clear_batch') ?>">
                <?= Loader::helper("validation/token")->output('clear_batch') ?>
                <input type="hidden" name="id" value="<?= $batch->getID() ?>">
                <p><?= t('Are you sure you want to remove all content from this import batch? This cannot be undone.') ?></p>
                <div class="dialog-buttons">
                    <button class="btn btn-light float-left"
                            onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
                    <button class="btn btn-danger float-right"
                            onclick="$('#ccm-dialog-clear-batch form').submit()"><?= t('Clear Batch') ?></button>
                </div>
            </form>
        </div>
        </div>

        <div data-dialog-wrapper="delete-batch-items">
        <div id="ccm-dialog-delete-batch-items" class="ccm-ui">
            <form method="post" action="<?= $view->action('delete_batch_items') ?>">
                <?= Loader::helper("validation/token")->output('delete_batch_items') ?>
                <input type="hidden" name="id" value="<?= $batch->getID() ?>">
                <p><?= t('Are you sure you want to remove the selected content from this import batch? This cannot be undone.') ?></p>
                <div class="dialog-buttons">
                    <button class="btn btn-light float-left"
                            onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
                    <button class="btn btn-danger float-right"
                            data-action="remove-selected-items"><?= t('Delete Selected') ?></button>
                </div>
            </form>
        </div>
        </div>


        <div data-dialog-wrapper="clear-batch-mappings">
        <div id="ccm-dialog-clear-batch-mappings" class="ccm-ui">
            <form method="post" action="<?= $view->action('clear_batch_mappings') ?>">
                <?= Loader::helper("validation/token")->output('clear_batch_mappings') ?>
                <input type="hidden" name="id" value="<?= $batch->getID() ?>">
                <p><?= t('Are you sure you reset all mapped content items for this batch? Any presets you have uploaded will not be removed.') ?></p>
                <div class="dialog-buttons">
                    <button class="btn btn-secondary float-left"
                            onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
                    <button class="btn btn-danger float-right"
                            onclick="$('#ccm-dialog-clear-batch-mappings form').submit()"><?= t('Clear Batch Mappings') ?></button>
                </div>
            </form>
        </div>
        </div>

        <div data-dialog-wrapper="reset-processes">
            <div id="ccm-dialog-reset-processes" class="ccm-ui">
                <form method="post" action="<?= $view->action('reset_processes') ?>">
                    <?= Loader::helper("validation/token")->output('reset_processes') ?>
                    <input type="hidden" name="id" value="<?= $batch->getID() ?>">
                    <p><?= t('Are you sure you reset all running processes for this batch? If someone else is actively importing content into the site it could affect them.') ?></p>
                    <div class="dialog-buttons">
                        <button class="btn btn-secondary"
                                onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
                        <button class="btn btn-danger"
                                onclick="$('#ccm-dialog-reset-processes form').submit()"><?= t('Reset Processes') ?></button>
                    </div>
                </form>
            </div>
        </div>



<div data-dialog-wrapper="create-content">
        <div id="ccm-dialog-create-content" class="ccm-ui">
            <form method="post">
                <p data-description="create-content"><?= t('Create site content from the contents of this batch?') ?></p>

                <div class="dialog-buttons">
                    <button class="btn btn-light float-left"
                            onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
                    <button class="btn btn-primary float-right"
                            data-action="publish-content"><?= t('Publish Batch') ?></button>
                </div>
            </form>
        </div>
        </div>


        <div data-dialog-wrapper="add-to-batch">
        <div id="ccm-dialog-add-to-batch" class="ccm-ui">
            <form method="post" action="<?= $view->action('add_content_to_batch') ?>" enctype="multipart/form-data">
                <?= Loader::helper("validation/token")->output('add_content_to_batch') ?>
                <input type="hidden" name="id" value="<?= $batch->getID() ?>">
                <div class="form-group">
                    <?= Loader::helper("form")->label('file', t('Content File')) ?>
                    <?= Loader::helper('form')->file('file') ?>
                </div>
                <div class="form-group">
                    <?= Loader::helper("form")->label('format', t('File Format')) ?>
                    <?= Loader::helper('form')->select('format', $formats) ?>
                </div>
                <div class="form-group">
                    <?= Loader::helper("form")->label('method', t('Records')) ?>
                    <div class="radio">
                        <label><input type="radio" name="importMethod" value="replace"
                                      checked> <?= t('Replace all batch content.') ?></label>
                    </div>
                    <div class="radio">
                        <label><input type="radio" name="importMethod" value="append"> <?= t('Add content to batch.') ?>
                        </label>
                    </div>
                </div>
            </form>
            <div class="dialog-buttons">
                <button class="btn btn-secondary float-left"
                        onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
                <button class="btn btn-primary float-right" data-action="add-content"><?= t('Add Content') ?></button>
            </div>
        </div>
        </div>
    </div>


    <h2><?= t('Batch') ?>
        <small><?= $dh->formatDateTime($batch->getDate(), true) ?></small>
    </h2>

    <h3><?=t('Name')?></h3>
    <?php if ($batch->getName()) { ?>
        <p><?= $batch->getName() ?></p>
    <?php } else { ?>
        <p><?=t('None')?></p>
    <?php }

    $site = $batch->getSite();
    if (is_object($site) && !$site->isDefault()) { ?>
        <h3><?= t('Site') ?></h3>
        <p><?= $site->getSiteName() ?></p>
    <?php } ?>

<?php if ($activeProcesses) { ?>

    <?=$activeProcesses->render();?>

<?php } else { ?>
    <?php Loader::element('batch', array('batch' => $batch), 'migration_tool'); ?>
<?php } ?>

<script type="text/javascript">

    rescanBatchItems = function () {

        jQuery.fn.dialog.showLoader();
        var data = [];
        data.push({'name': 'id', 'value': '<?=$batch->getID()?>'});
        data.push({'name': 'ccm_token', 'value': '<?=Loader::helper('validation/token')->generate('rescan_batch_items')?>'});
        $.concreteAjax({
            data: data,
            url: '<?=$view->action('rescan_batch_items')?>',
            success: function() {
                window.location.reload();
            }
        });


    }

    $(function () {

        $('a[data-action=rescan-batch]').on('click', function (e) {
            e.preventDefault();
            rescanBatchItems();
        });

        $('button[data-action=publish-content]').on('click', function (e) {
            $('p[data-description=create-content]').hide();
            $('div[data-progress-bar=create-content]').show();
            $('div[data-progress-bar=create-content] h4').html('<?=t('Publishing Content...')?>');

            new ConcreteProgressiveOperation({
                url: '<?=$view->action('create_content_from_batch')?>',
                data: [
                    {'name': 'id', 'value': '<?=$batch->getID()?>'},
                    {
                        'name': 'ccm_token',
                        'value': '<?=Core::make('token')->generate('create_content_from_batch')?>'
                    }
                ],
                onComplete: function() {
                    window.location.reload();
                },
                element: $('div[data-progress-bar-wrapper=create-content]')
            });
        });

        $('button[data-action=remove-selected-items]').on('click', function (e) {
            var data = $('input[data-checkbox=select-item]').serializeArray();
            jQuery.fn.dialog.showLoader();
            data.push({'name': 'id', 'value': '<?=$batch->getID()?>'});
            data.push({'name': 'ccm_token', 'value': '<?=Loader::helper('validation/token')->generate('delete_batch_items')?>'});
            $.concreteAjax({
                data: data,
                url: '<?=$view->action('delete_batch_items')?>',
                success: function() {
                    window.location.reload();
                }
            });
        });


        var uploadErrors = [];


        $("button[data-action=add-content]").on('click.uploadFile', function () {
            var submitSuccess = false;
            $('div[data-progress-bar=add-to-batch]').show();
            $('div[data-progress-bar=add-to-batch] h4').html('<?=t('Uploading File...')?>');
            $('#ccm-dialog-add-to-batch form').concreteAjaxForm({
                beforeSubmit: function () {
                    // Nothing - we don't want the loader
                },
                success: function (r) {
                    window.location.reload();
                },
                complete: function () {
                    if (!submitSuccess) {
                        $('div[data-progress-bar=add-to-batch]').hide();
                        $('div[data-progress-bar=add-to-batch] h4').html('');
                    }
                }
            }).submit();

        });

    });

</script>

<style type="text/css">
    #ccm-migration-batch-bulk-errors li {
        position: relative;
        padding-left: 35px
    }

    #ccm-migration-batch-bulk-errors li i {
        position: absolute;
        top: 5px;
        left: 0px;
        width: 30px;
        text-align: center;
    }
</style>
