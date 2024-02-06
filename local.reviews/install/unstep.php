<?php

/**
 * @global $APPLICATION
 */

use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

if ($errorException = $APPLICATION->GetException()) {
    CAdminMessage::ShowMessage('Ошибка при удалении модуля: ' . $errorException->GetString());
} else {
    CAdminMessage::ShowNote('Модуль удален');
}
?>

<form action="<?= $APPLICATION->GetCurPage(); ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>"/>
    <input type="submit" value="Вернуться к списку модулей">
</form>