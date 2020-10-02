<?php

use Bitrix\Sale;

/**
 * Savebasket component class
 *
 * @author dimabresky
 */
class SavebasketComponent extends CBitrixComponent {

    function prepareParameters() {
        $this->arParams['LIST_PAGE'] = $this->arParams['LIST_PAGE'] ?: '/personal/order/savebasket/';
    }

    function executeComponent() {


        if ($this->request->get('savebasket') === 'Y') {
            $this->saveBasket();
        }

        $this->includeComponentTemplate();
    }

    function saveBasket() {

        Bitrix\Main\Loader::includeModule('sale');
        Bitrix\Main\Loader::includeModule('dky.savebasket');

        $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());

        $basketItems = $basket->getBasketItems();

        if ($basket->isEmpty()) {
            return;
        }

        $arLastRow = \dky\SavebasketTable::getList(['order' => ['ID' => 'DESC'], 'limit' => 1, 'select' => ['ID']])->fetch();
        
        $basketId = $arLastRow['ID'] ? ++$arLastRow['ID'] : 1;

        $fuserId = Sale\Fuser::getId();

        $date = Bitrix\Main\Type\DateTime::createFromTimestamp(time());
        foreach ($basketItems as $item) {

            \dky\SavebasketTable::add([
                'BASKET_ID' => $basketId,
                'FUSER_ID' => $fuserId,
                'PRODUCT_ID' => $item->getProductId(),
                'QUANTITY' => $item->getQuantity(),
                'DATE' => $date
            ]);

            $item->delete();
        }


        $basket->save();

        LocalRedirect($this->arParams['LIST_PAGE']);
    }

}
