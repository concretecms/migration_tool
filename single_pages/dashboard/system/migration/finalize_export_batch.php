<?php
defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Page\View\PageView $view
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var Concrete\Core\Form\Service\Form $form	
 *
 * @var PortlandLabs\Concrete5\MigrationTool\Entity\Export\Batch $batch
 * @var Concrete\Core\Entity\File\File[] $files
 * @var string $xml
 */

?>
<div class="ccm-dashboard-header-buttons">
    <a href="<?= h($view->action('view_batch', $batch->getID())) ?>" class="btn btn-default"><i class="fa fa-angle-double-left"></i> <?= t('Back to Batch') ?></a>
</div>

<?php
if ($files !== []) {
    ?>
    <script>
        $(function() {
            $('input[data-checkbox=select-all]').on('click', function() {
                if ($(this).is(':checked')) {
                    $('tbody input[type=checkbox]:enabled').prop('checked', true);
                } else {
                    $('tbody input[type=checkbox]:enabled').prop('checked', false);
                }
                $('tbody input[type=checkbox]:enabled').trigger('change');
            });

            $('tbody input[type=checkbox]').on('change', function() {
                if ($('tbody input[type=checkbox]:checked').length) {
                    $('button[data-action=download-files]').prop('disabled', false);
                } else {
                    $('button[data-action=download-files]').prop('disabled', true);
                }
            });

        });
    </script>
    <form method="POST" action="<?= h($view->action('download_files')) ?>">
        <?php $token->output('download_files') ?>
        <input type="hidden" name="id" value="<?= $batch->getID() ?>" />
        <button style="float: right" disabled class="btn btn-xs btn-default" data-action="download-files" type="submit"><?= t('Download Files') ?></button>
        <h3><?= t('Files') ?></h3>
        <table class="table table-striped zebra-striped">
            <thead>
                <tr>
                    <th><input type="checkbox" data-checkbox="select-all"></th>
                    <th><?= t('ID') ?></th>
                    <th style="width: 100%"><?= t('Filename') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($files as $file) {
                    ?>
                    <tr>
                        <td><input type="checkbox" data-checkbox="batch-file" name="batchFileID[]" value="<?= $file->getFileID() ?>"></td>
                        <td><?= $file->getFileID() ?></td>
                        <td><?= h($file->getFileName()) ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </form>
    <?php
} else {
    ?>
    <h3><?= t('Files') ?></h3>
    <p><?= t('No referenced files found.') ?></p>
    <?php
}
?>
<h3><?= t('Content XML') ?></h3>
<div class="btn-group">
    <button type="button" id="mt-export-xml-view" class="btn btn-secondary"><?= t('View XML') ?></button>
    <button type="button" id="mt-export-xml-download" class="btn btn-secondary"><?= t('Download XML') ?></button>
</div>
<script>
addEventListener('DOMContentLoaded', () => {

const XML = <?= json_encode($xml) ?>;
function sendXML(download)
{
    const blob = new Blob([XML], {type: 'application/xml; charset=utf-8'});
    const url = URL.createObjectURL(blob);
    if (download) {
        const a = document.createElement('a');
        a.href = url;
        a.download = 'export.xml';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    } else {
        window.open(url, '_blank');
        setTimeout(() => URL.revokeObjectURL(url), 15000);
    }
}    

document.querySelector('#mt-export-xml-view').addEventListener('click', (e) => {
    e.preventDefault();
    sendXML(false);
});

document.querySelector('#mt-export-xml-download').addEventListener('click', (e) => {
    e.preventDefault();
    sendXML(true);
});

});
</script>
