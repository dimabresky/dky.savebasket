<?php

namespace dky;

use Bitrix\Main\Entity;

/**
 * savebasket table orm class
 *
 * @author dimabresky
 */
class SavebasketTable extends Entity\DataManager {

    static function getTableName() {
        return 'savebasket';
    }

    static function getMap() {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true
                    )),
            new Entity\IntegerField('FUSER_ID'),
            new Entity\IntegerField('PRODUCT_ID'),
            new Entity\IntegerField('BASKET_ID'),
            new Entity\IntegerField('QUANTITY'),
            new Entity\DateTimeField('DATE'),
            new Entity\DateTimeField('SHIPPING_DATE'),
        );
    }

}
