<?php
CJSCore::Init(['ajax', 'date']);
\Bitrix\Main\UI\Extension::load("ui.vue");
?>
<div class="row">
    <div class="col-md-3 col-sm-3 col-xs-12">

        <div class="bx_sitemap"><ul class="bx_sitemap_ul">
                <li>
                    <h2 class="bx_sitemap_li_title">

                        <a href="/personal/order/">Текущие заказы</a>
                    </h2>
                </li>
                <li>
                    <h2 class="bx_sitemap_li_title">

                        <a href="/personal/order/?filter_history=Y">Выполненные</a>
                    </h2>
                </li>
                <li>
                    <h2 class="bx_sitemap_li_title">

                        <a href="/personal/order/?filter_history=Y&show_canceled=Y">Отмененные</a>
                    </h2>
                </li>
                <li>
                    <h2 class="bx_sitemap_li_title">

                        <a href="/personal/savebasket/">Сохраненные корзины</a>
                    </h2>
                </li>
                <li>
                    <h2 class="bx_sitemap_li_title">

                        <a href="/personal/cart/">Корзина</a>
                    </h2>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-md-9 col-sm-9 col-xs-12">
        <?php if (empty($arResult['BASKETS'])): ?>
            <div class="alert alert-danger">Сохраненных корзин не обнаружено</div>
        <?php else: ?>
            <? // foreach ($arResult['BASKETS'] as $basketid => $arBasket): ?>
            <? // foreach ($arBasket as $arItem): ?>
            <template id="basket-template">
                <div>
                    <div v-for="basket in BASKETS" class="col-md-12 col-sm-12 sale-order-list-container">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 sale-order-list-accomplished-title-container">
                                <div class="row">
                                    <div class="col-md-8 col-sm-12 sale-order-list-accomplished-title-container">
                                        <h2 class="sale-order-list-accomplished-title">
                                            Корзина № {{basket.ID}} от {{basket.DATE}}, товаров: {{basket.QUANTITY}} на сумму {{basket.TOTAL_PRICE}} {{basket.CURRENCY}}
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 sale-order-list-inner-accomplished">
                                <div class="row sale-order-list-inner-row">
                                    <div class="col-md-3 col-sm-12 sale-order-list-about-accomplished">
                                        <a @click="deleteBasket(event, basket.ID)" class="sale-order-list-about-link" href="javascript:void(0)">
                                            Удалить                                        
                                        </a><br>

                                        <a @click="printBasket(event, basket.ID)" class="sale-order-list-about-link" href="javascript:void(0)">
                                            Распечатать                                        
                                        </a><br>

                                        <a @click="showProducts(event, basket)" class="sale-order-list-about-link" href="javascript:void(0)">
                                            Состав заказа                                         
                                        </a>
                                    </div>
                                    <div class=" col-md-3 col-md-offset-6 col-sm-12 sale-order-list-repeat-accomplished">
                                        <div class="shpping-date-container">
                                            <a v-if="!basket.SHIPPING_DATE.EDIT" @click="basket.SHIPPING_DATE.EDIT = !basket.SHIPPING_DATE.EDIT" class="sale-order-list-about-link" href="javascript:void(0)">
                                                Желаемое время отгрузки <span v-if="!basket.SHIPPING_DATE.EDIT && basket.SHIPPING_DATE.VALUE">{{basket.SHIPPING_DATE.VALUE}}</span>
                                            </a>
                                            <div class="shpping-date-container__edit shipping-date-edit" v-if="basket.SHIPPING_DATE.EDIT">
                                                <input :id="getShippingDateInputId(basket.ID)" :value="basket.SHIPPING_DATE.VALUE" onclick="BX.calendar({node: this, field: this, bTime: true})"> <br> <button @click="saveShippingDate(event, basket.ID)" class="shipping-date-edit__btn btn btn-success btn-sm">Сохранить</button> <button @click="basket.SHIPPING_DATE.EDIT = !basket.SHIPPING_DATE.EDIT" class="shipping-date-edit__btn btn btn-danger btn-sm">Отменить</button>
                                            </div>
                                        </div>
                                        <button @click="makeOrder(event, basket.ID)" class="btn btn-success">Оформить заказ</button>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="!basket.HIDDEN_ITEMS" v-for="item in basket.ITEMS" :data-id="basket.ID" class="row">
                            <div class="col-md-12 sale-order-list-inner-accomplished">
                                <div class="basket-item-picture">
                                    <img :src="PRODUCTS_DETAIL_INFO_GROUPED_BY_ID && PRODUCTS_DETAIL_INFO_GROUPED_BY_ID[item.PRODUCT_ID] && PRODUCTS_DETAIL_INFO_GROUPED_BY_ID[item.PRODUCT_ID].DETAIL_PICTURE ? PRODUCTS_DETAIL_INFO_GROUPED_BY_ID[item.PRODUCT_ID].DETAIL_PICTURE : '<?= SITE_TEMPLATE_PATH ?>/images/noimage.png'" width="50">
                                </div>
                                <div class="basket-item-description">
                                    <div class="basket-item-description__name basket-item-description__field">{{item.NAME}}</div>
                                    <div class="basket-item-description__quantity basket-item-description__field">Количество: {{item.QUANTITY}}</div>
                                    <div class="basket-item-description__price basket-item-description__field">Цена: {{item.PRICE}} {{item.CURRENCY}}</div>
                                </div>
                                <div class="clearfix"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </template>
            <div id="baskets">

            </div>
            <script>
                (function () {

                    BX.Vue.create({
                        el: "#baskets",
                        data: <?= json_encode($arResult) ?>,
                        template: document.getElementById('basket-template').innerHTML,

                        methods: {
                            showProducts(e, basket) {
                                basket.HIDDEN_ITEMS = !basket.HIDDEN_ITEMS;
                                this.$nextTick(function () {

                                    if (e.target && e.target.scrollIntoView) {
                                        e.target.scrollIntoView({behavior: "smooth", block: "center"});
                                    }
                                });
                            },
                            getShippingDateInputId(basketid) {
                                return `shipping-date-input-${basketid}`;
                            },
                            saveShippingDate(e, basketid) {
                                e.target.innerText = "Сохранение...";
                                let shippingDate = BX(this.getShippingDateInputId(basketid)).value;
                                BX.ajax.runComponentAction('dky:savebasket.list', 'saveshippingdate', {
                                    mode: 'class',
                                    data: {
                                        basketid: basketid,
                                        shippingDate: shippingDate
                                    }
                                }).then(r => {

                                    this.BASKETS.forEach(item => {
                                        if (item.ID == basketid) {
                                            item.SHIPPING_DATE.EDIT = false;
                                            item.SHIPPING_DATE.VALUE = shippingDate;
                                            return false;
                                        }
                                    });



                                    e.target.innerText = "Сохранить";
                                }).catch(() => {
                                    e.target.innerText = "Сохранить";
                                    alert('Ошибка при попытке сохранения даты отгрузки');
                                });
                            },
                            makeOrder(e, basketid) {
                                e.target.innerText = "Подготовка данных...";
                                BX.ajax.runComponentAction('dky:savebasket.list', 'createbasket', {
                                    mode: 'class',
                                    data: {
                                        basketid: basketid
                                    }
                                }).then(r => {

                                    location.href = '/personal/order/make/';

                                }).catch(() => {
                                    e.target.innerText = "Оформить заказ";
                                    alert('Ошибка при попытке оформить заказ');
                                });
                            },
                            deleteBasket(e, basketid) {

                                if (confirm('Данная сохраненная корзина будет удалена. Продолжить ?')) {

                                    e.target.innerText = "Удаление...";
                                    BX.ajax.runComponentAction('dky:savebasket.list', 'delete', {
                                        mode: 'class',
                                        data: {
                                            basketid: basketid
                                        }
                                    }).then(r => {

                                        location.reload();

                                    }).catch(() => {
                                        e.target.innerText = "Удалить";
                                        alert('Ошибка при попытке удаления корзины');
                                    });

                                }

                            },
                            printBasket(e, basketid) {
                                location.href = '/personal/savebasket/?print=' + basketid;
                            },
                        },
                    });
                })();
            </script>

            <? // endforeach   ?>
            <? // endforeach ?>
        <?php endif; ?>
    </div>
</div>


