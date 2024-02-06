<?php

use Bitrix\Iblock\Component\Tools;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\InheritedProperty\ElementValues;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class ReviewsComponent extends CBitrixComponent
{
    /** @var array $reviewsModuleParams */
    private array $reviewsModuleParams;

    /**
     * @param CBitrixComponent|null $component
     *
     * @throws LoaderException
     * @throws ArgumentNullException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        if (!Loader::includeModule('iblock')) {
            ShowError('Не подключен модуль iblock');
            return;
        }

        parent::__construct($component);

        $this->reviewsModuleParams = Option::getForModule('local.reviews');
    }

    /**
     * @param $arParams
     *
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        $arParams['CACHE_TIME'] = (int)($arParams['CACHE_TIME'] ?? 36000000);

        return $arParams;
    }

    /**
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function executeComponent(): void
    {
        if (!$this->isExistIblock($this->reviewsModuleParams['IBLOCK_ID'])) {
            ShowError('Отсутствует инфоблок с отзывами');
            return;
        }

        if ($this->startResultCache(false, [])) {
            $this->arResult['REVIEWS'] = $this->getReviews();
            $this->endResultCache();
        }

        $this->includeComponentTemplate();
    }

    /**
     * @return array
     */
    private function getReviews(): array
    {
        $iblockId = $this->reviewsModuleParams['IBLOCK_ID'];

        $reviewQuery = CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => $iblockId,
            ],
            false,
            false,
            [
                'ID',
                'PREVIEW_TEXT',
                'PREVIEW_PICTURE',
                'PROPERTY_NAME',
            ]
        );
        $reviews = [];

        while ($review = $reviewQuery->fetch()) {
            $inheritedPropertyValues = (new ElementValues($iblockId, $review['ID']))->getValues();
            $review['INHERITED_PROPERTY_VALUES'] = $inheritedPropertyValues;

            Tools::getFieldImageData(
                $review,
                ['PREVIEW_PICTURE'],
                Tools::IPROPERTY_ENTITY_ELEMENT,
                'INHERITED_PROPERTY_VALUES',
            );

            $reviews[$review['ID']] = $review;
        }

        return $reviews;
    }

    /**
     * @param int $iblockId
     *
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function isExistIblock(int $iblockId): bool
    {
        $iblock = IblockTable::getList(['filter' => ['ID' => $iblockId]])->fetch();
        return (bool)$iblock;
    }
}