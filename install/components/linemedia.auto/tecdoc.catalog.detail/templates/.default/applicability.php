<?__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));?>
<? foreach ($arResult['APPLICABILITY'] as $model) { ?>
    <a href="javascript:void(0)" class="applicability-model" rel="<?= md5($model['MODEL_NAME']) ?>"><?= $model['MODEL_NAME'] ?></a>
<? } ?>
<div class="clear"></div>

    <? foreach ($arResult['APPLICABILITY'] as $model) { ?>
        <div id="applicability-modification-<?= md5($model['MODEL_NAME']) ?>" class="applicability-modifications" style="display: none;">
            <table class="applicability-modifications-table">
                <thead>
                    <tr>
                        <th><?= GetMessage('HEAD_TYPE') ?></th>
                        <th><?= GetMessage('HEAD_YEAR') ?></th>
                        <th><?= GetMessage('HEAD_KILOWATTS') ?></th>
                        <th><?= GetMessage('HEAD_HORSEPOWER') ?></th>
                        <th><?= GetMessage('HEAD_VOLUME') ?></th>
                        <th><?= GetMessage('HEAD_FORM_ASSEMBLING') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($model['MODIFICATIONS'] as $modification) { ?>
                        <tr>
                            <td align="center">
                                <?= $modification['carDesc'] ?>
                            </td>
                            <td align="right">
                                <?= substr($modification['yearOfConstructionFrom'], -2, 2) ?>.<?= substr($modification['yearOfConstructionFrom'], 0, 4) ?>
                            </td>
                            <td align="right">
                                <?= $modification['powerKwFrom'] ?>
                            </td>
                            <td align="right">
                                <?= $modification['powerHpFrom'] ?>
                            </td>
                            <td align="right">
                                <?= $modification['cylinderCapacity'] ?>
                            </td>
                            <td align="right">
                                <?= $modification['constructionType'] ?>
                            </td>
                        </tr> 
                    <? } ?>
                </tbody>
            </table>
        </div>
    <? } ?>
