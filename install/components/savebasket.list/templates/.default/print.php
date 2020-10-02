<?php
if (!$arResult['PRINT_BASKET']) {
    ShowError('Корзина не найдена.');
    return;
}

$count = count($arResult['PRINT_BASKET']['ITEMS']);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Корзина товаров</title>
        <style>
            table {
                border-collapse: collapse;
            }
            table, th, td {
                border: 1px solid black;
            }
            td {
                padding: 10px;
            }
        </style>
    </head>
    <body>
        <DIV>
            <?PHP
            echo "Пользователь: " . ($USER->GetLogin() ?: 'Unknown') . "<br>";
            echo "Дата: " . date('d.m.Y') . "<br><br>";
            ?>
        </DIV>
        <table>
            <thead>
                <tr>
                    <th>Номер заявки</th>
                    <th>Дата сохранения корзины</th>
                    <th>Дата желаемой отгрузки</th>
                    <th>Наименование товара</th>
                    <th>Количество</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td rowspan="<?= $count ?>"><?= htmlspecialchars($_REQUEST['print']) ?></td>
                    <td rowspan="<?= $count ?>"><?= $arResult['PRINT_BASKET']['ITEMS'][0]['DATE'] ?: '' ?></td>
                    <td rowspan="<?= $count ?>"><?= $arResult['PRINT_BASKET']['ITEMS'][0]['SHIPPING_DATE'] ?: '' ?></td>
                    <td><?= $arResult['PRINT_BASKET']['ITEMS'][0]['NAME'] ?></td>
                    <td><?= $arResult['PRINT_BASKET']['ITEMS'][0]['QUANTITY'] ?></td>
                </tr>
                <? for ($i = 1; $i < $count; $i++): ?>
                    <tr>
                        <td><?= $arResult['PRINT_BASKET']['ITEMS'][$i]['NAME'] ?></td>
                        <td><?= $arResult['PRINT_BASKET']['ITEMS'][$i]['QUANTITY'] ?></td>
                    </tr>
                <? endfor ?>
            </tbody>
        </table>
    </body>
</html>