<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? IncludeTemplateLangFile(__FILE__); ?>
            </div>
        </div>
    </div>
</div>

<div id="footer-wrapper" class="row footer">
    <div class="span6">
        <?  // Копирайт.
            $APPLICATION->IncludeComponent(
                "bitrix:main.include",
                "",
                array(
                    "AREA_FILE_SHOW" => "file",
                    "PATH" => SITE_DIR."include/copyright.php"
                ),
                false
            );
        ?>
    </div>
    <div class="span3">&nbsp;</div>
    <div class="span3">
        <?= GetMessage('FOOTER_DISIGN') ?>
    </div>
</div>

</div>
</div>
</div>
</div>

</div>

</html>