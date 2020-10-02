<?php

use Bitrix\Sale;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;

/**
 * SavebasketList component class
 *
 * @author dimabresky
 */
class SavebasketListComponent extends CBitrixComponent implements Controllerable {

    function configureActions(): array {
        return [
            'delete' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                            array(ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf()
                ]
            ],
            'createbasket' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                            array(ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf()
                ]
            ],
            'saveshippingdate' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                            array(ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf()
                ]
            ]
        ];
    }

    /**
     * @param int $basketid
     * @return array
     */
    function deleteAction($basketid) {

        $this->includeModules();

        $dbList = dky\SavebasketTable::getList([
                    'filter' => [
                        'FUSER_ID' => Sale\Fuser::getId(),
                        'BASKET_ID' => $basketid
                    ],
                    'select' => ['ID']
        ]);

        while ($arRow = $dbList->fetch()) {

            dky\SavebasketTable::delete($arRow['ID']);
        }

        return [];
    }

    /**
     * @param int $basketid
     * @return array
     */
    function createbasketAction($basketid) {
        $this->includeModules();

        $dbList = dky\SavebasketTable::getList([
                    'filter' => [
                        'FUSER_ID' => Sale\Fuser::getId(),
                        'BASKET_ID' => $basketid
                    ]
        ]);

        $arBasketItems = $dbList->fetchAll();

        $basket = $this->createBasket($arBasketItems, true);
        $basket->save();

        foreach ($arBasketItems as $arItem) {
            dky\SavebasketTable::delete($arItem['ID']);
        }

        return [];
    }

    /**
     * @param int $basketid
     * @param string $shippingDate
     * @return array
     */
    function saveshippingdateAction($basketid, $shippingDate) {

        $this->includeModules();

        $dbList = dky\SavebasketTable::getList([
                    'filter' => [
                        'FUSER_ID' => Sale\Fuser::getId(),
                        'BASKET_ID' => $basketid
                    ],
                    'select' => ['ID']
        ]);

        while ($arRow = $dbList->fetch()) {

            dky\SavebasketTable::update($arRow['ID'], ['SHIPPING_DATE' => $shippingDate ? \Bitrix\Main\Type\DateTime::createFromUserTime($shippingDate) : null]);
        }

        return [];
    }

    function includeModules() {
        Bitrix\Main\Loader::includeModule('catalog');
        Bitrix\Main\Loader::includeModule('iblock');
        Bitrix\Main\Loader::includeModule('sale');
        Bitrix\Main\Loader::includeModule('dky.savebasket');
    }

    function createBasket(array $basketItems, bool $clearBefore = false) {

        $currency = Bitrix\Currency\CurrencyManager::getBaseCurrency();

        $basket = Sale\Basket::create(SITE_ID);

        if ($clearBefore) {
            $basket->clearCollection();
        }

        foreach ($basketItems as $arItem) {

            $arItem['CURRENCY'] = Bitrix\Currency\CurrencyManager::getBaseCurrency();
            $arItem['LID'] = SITE_ID;
            $arItem['PRODUCT_PROVIDER_CLASS'] = "CCatalogProductProvider";

            if ($item = $basket->getExistsItem('catalog', $arItem['PRODICT_ID'])) {
                $item->setField('QUANTITY', $item->getQuantity() + $arItem['QUANTITY']);
            } else {
                $item = $basket->createItem('catalog', $arItem['PRODUCT_ID']);
                $item->setFields(array(
                    'QUANTITY' => $arItem['QUANTITY'],
                    'CURRENCY' => $currency,
                    'LID' => Bitrix\Main\Context::getCurrent()->getSite(),
                    'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
                ));
            }
        }
        return $basket;
    }

    function executeComponent() {

        $this->includeModules();


        $this->arResult['BASKETS'] = [];
        $this->arResult['PRODUCTS_DETAIL_INFO_GROUPED_BY_ID'] = [];



        $dbList = dky\SavebasketTable::getList([
                    'order' => ['BASKET_ID' => 'DESC'],
                    'filter' => [
                        'FUSER_ID' => Sale\Fuser::getId()
                    ]
        ]);



        $arBasketGroupProducts = [];
        while ($arRow = $dbList->fetch()) {

            $arBasketGroupProducts[$arRow['BASKET_ID']][] = $arRow;
        }

        $arProductsId = [];
        foreach ($arBasketGroupProducts as $basketid => $arBasketItems) {

            $basket = $this->createBasket($arBasketItems);

            if ($basket->isEmpty()) {
                continue;
            }

            $this->arResult['BASKETS'][$basketid] = [
                'ID' => $basketid,
                'ITEMS' => [],
                'DATE' => $arBasketItems[0]['DATE']->toString(),
                'SHIPPING_DATE' => [
                    'EDIT' => false,
                    'VALUE' => $arBasketItems[0]['SHIPPING_DATE'] ? $arBasketItems[0]['SHIPPING_DATE']->toString() : ''
                ],
                'CURRENCY' => Bitrix\Currency\CurrencyManager::getBaseCurrency(),
                'TOTAL_PRICE' => round($basket->getPrice(), 2),
                'QUANTITY' => 0,
                'HIDDEN_ITEMS' => true
            ];

            foreach ($basket->getBasketItems() as $basketItem) {

                $quantity = $basketItem->getQuantity();

                $arProductsId[] = $basketItem->getProductId();

                $this->arResult['BASKETS'][$basketid]['ITEMS'][] = [
                    'DATE' => $arBasketItems[0]['DATE']->toString(),
                    'SHIPPING_DATE' => $arBasketItems[0]['SHIPPING_DATE'] ? $arBasketItems[0]['SHIPPING_DATE']->toString() : '',
                    'PRODUCT_ID' => $basketItem->getProductId(),
                    'PRICE' => $basketItem->getFinalPrice(),
                    'NAME' => $basketItem->getField('NAME'),
                    'CURRENCY' => $basketItem->getField('CURRENCY'),
                    'QUANTITY' => $quantity,
                    'AVAIL' => $basketItem->canBuy()
                ];

                $this->arResult['BASKETS'][$basketid]['QUANTITY'] += $quantity;
            }
        }

        $this->arResult['PRODUCTS_DETAIL_INFO_GROUPED_BY_ID'] = [];
        if (!empty($arProductsId)) {

            $dbElements = CIBlockElement::GetList(false, ['ID' => array_unique($arProductsId)], false, false);
            while ($arFields = $dbElements->Fetch()) {

                if ($arFields['DETAIL_PICTURE']) {
                    $arFields['DETAIL_PICTURE'] = CFile::GetFileArray($arFields['DETAIL_PICTURE'])['SRC'];
                } else {
                    $arParentProductId = CCatalogSku::GetProductInfo(
                                    $arFields['ID']
                    );

                    if ($arParentProductId) {
                        $arParentProduct = CIBlockElement::GetList(false, ['ID' => $arParentProductId['ID']], false, false, ['DETAIL_PICTURE', 'ID'])->Fetch();
                        if ($arParentProduct && $arParentProduct['DETAIL_PICTURE']) {

                            $arFields['DETAIL_PICTURE'] = CFile::GetFileArray($arParentProduct['DETAIL_PICTURE'])['SRC'];
                        }
                    }
                }

                $this->arResult['PRODUCTS_DETAIL_INFO_GROUPED_BY_ID'][$arFields['ID']] = $arFields;
            }
        }

        if ($this->request->get('print') && $this->arResult['BASKETS'][$this->request->get('print')]) {
            $this->arResult['PRINT_BASKET'] = $this->arResult['BASKETS'][$this->request->get('print')];
            $GLOBALS['APPLICATION']->RestartBuffer();
            $this->includeComponentTemplate('print');
            die;
        }

        $this->arResult['BASKETS'] = array_values($this->arResult['BASKETS']);

        $this->includeComponentTemplate();
    }

}
