<?php

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ModuleManager;

class local_reviews extends CModule
{
    public $MODULE_ID = 'local.reviews';
    public $MODULE_NAME = 'Local: Отзывы';
    public $MODULE_DESCRIPTION = 'Local: Отзывы';

    public function __construct()
    {
        if (is_file(__DIR__ . '/version.php')) {
            include_once(__DIR__ . '/version.php');

            /** @var array $arModuleVersion */
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        } else {
            CAdminMessage::ShowMessage('Файл version.php не найден');
        }
    }

    /**
     * @return void
     * @throws ArgumentOutOfRangeException
     * @throws LoaderException
     */
    public function doInstall(): void
    {
        global $APPLICATION;
        ModuleManager::registerModule($this->MODULE_ID);

        $result = $this->installDB();

        if ($result) {
            $this->InstallFiles();
        }

        $APPLICATION->IncludeAdminFile(
            "Установка модуля  $this->MODULE_ID",
            __DIR__ . '/step.php',
        );
    }

    /**
     * @return bool
     * @throws ArgumentOutOfRangeException
     * @throws LoaderException
     */
    public function installDB(): bool
    {
        global $APPLICATION;

        if (!Loader::includeModule('iblock')) {
            $APPLICATION->ThrowException('Не подключен модуль iblock');
            return false;
        }

        if (!$this->addIblockType()) {
            return false;
        }

        if (!$this->addIblock()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function installFiles(): bool
    {
        global $APPLICATION;

        if (!CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/components',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/' . $this->MODULE_ID . '/',
            true,
            true
        )) {
            $APPLICATION->ThrowException('Ошибка при копировании компонентов модуля');
            return false;
        }

        return true;
    }

    /**
     * @return bool
     * @throws ArgumentOutOfRangeException
     */
    private function addIblock(): bool
    {
        global $APPLICATION;
        $iblock = $this->getIblock();

        $iblockEntity = new CIBlock();
        $iblockId = $iblockEntity->Add($iblock);

        if (!$iblockId) {
            $APPLICATION->ThrowException('Ошибка при создании инфоблока');
            return false;
        }

        Option::set($this->MODULE_ID, 'IBLOCK_ID', $iblockId);

        if (!$this->addIblockProperties($iblockId)) {
            return false;
        }

        return true;
    }

    /**
     * @param int $iblockId
     *
     * @return bool
     */
    private function addIblockProperties(int $iblockId): bool
    {
        global $APPLICATION;
        $properties = $this->getIblockProperties();

        foreach ($properties as $property) {
            $property['IBLOCK_ID'] = $iblockId;

            $iblockPropertyEntity = new CIBlockProperty();
            $iblockPropertyId = $iblockPropertyEntity->Add($property);

            if (!$iblockPropertyId) {
                $APPLICATION->ThrowException('Ошибка при создании свойства инфоблока');
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function addIblockType(): bool
    {
        global $APPLICATION;
        $iblockType = $this->getIblockType();

        $iblockTypeEntity = new CIBlockType();
        $iblockTypeId = $iblockTypeEntity->Add($iblockType);

        if (!$iblockTypeId) {
            $APPLICATION->ThrowException('Ошибка при создании типа инфоблока');
            return false;
        }

        return true;
    }

    /**
     * @return void
     * @throws LoaderException
     */
    public function doUninstall(): void
    {
        global $APPLICATION;

        $this->unInstallDB();
        $this->unInstallFiles();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            "Удаление модуля  $this->MODULE_ID",
            __DIR__ . '/unstep.php',
        );
    }

    /**
     * @return void
     * @throws LoaderException
     */
    public function unInstallDB(): void
    {
        global $APPLICATION;

        if (!Loader::includeModule('iblock')) {
            $APPLICATION->ThrowException('Не подключен модуль iblock');
            return;
        }

        $this->deleteIblockType();
    }

    /**
     * @return void
     */
    private function deleteIblockType(): void
    {
        global $APPLICATION;
        $iblockType = $this->getIblockType();

        $isIblockTypeDeleted = CIBlockType::Delete($iblockType['ID']);

        if (!$isIblockTypeDeleted) {
            $APPLICATION->ThrowException('Ошибка при удалении инфоблока');
        }
    }

    /**
     * @return void
     */
    public function unInstallFiles(): void
    {
        DeleteDirFilesEx('/bitrix/components/' . $this->MODULE_ID . '/');
    }

    /**
     * @return array[]
     */
    private function getIblock(): array
    {
        return
            [
                'LID' => 's1',
                'CODE' => 'reviews',
                'NAME' => 'Отзывы',
                'IBLOCK_TYPE_ID' => 'reviews',
                'FIELDS' =>
                    [
                        'PREVIEW_PICTURE' =>
                            [
                                'IS_REQUIRED' => 'Y',
                            ],
                        'PREVIEW_TEXT' =>
                            [
                                'IS_REQUIRED' => 'Y',
                            ],
                    ],
            ];
    }

    /**
     * @return array
     */
    private function getIblockProperties(): array
    {
        return
            [
                [
                    'NAME' => 'Имя',
                    'CODE' => 'NAME',
                    'PROPERTY_TYPE' => PropertyTable::TYPE_STRING,
                ],
            ];
    }

    /**
     * @return array[]
     */
    private function getIblockType(): array
    {
        return
            [
                'ID' => 'reviews',
                'IBLOCK_TYPE_ID' => 'reviews',
                'SECTIONS' => 'N',
                'LANG' =>
                    [
                        'ru' =>
                            [
                                'NAME' => 'Отзывы',
                                'ELEMENT_NAME' => 'Отзывы',
                            ],
                    ],
            ];
    }
}
