<?php
require_once "Config.php";
require_once "KaznacheyApi.php";

header('Content-Type: text/html; charset=utf-8');

/*
  Для работы с платёжной системой, необходимо:
  0) Включить CURL на сервере
  1) Получить уникальный и секретный ключ мерчанта.
  2) Запросить информацию о мерчанте.
  3) На основании информации о мерчанте сформировать запрос на создание платежа.
  4) Обработать ответ о состоянии платежа от сервера.
 */

$api = new KaznacheyApi(KAZNACHEY_SECRET_KEY, KAZNACHEY_GUID);

//Запрос на получение списка платежных систем доступных мерчанту.
//Возвращает список платежных систем и ссылку на условия использования для покупателей
////PaySystems - списко платежных систем
//  Информация о платежной системе:
//      Id - Идентификатор платежной системы
//      PaySystemName - Описание платёжной системы
//      PaySystemTag - Сокращеное название платежной системы на латиннице
////TermToUse - Ссылка на условия использования для покупателей
////ErrorCode - Код ошибки в системе (0 - успешный запрос)
////DebugMessage - Описание ошибки
$merchantInfo = $api->GetMerchantInfo();

if ($_POST["action"] == "create_payment") {
    //Запрос на создание платежа.
    $request = Array(
        "SelectedPaySystemId" => $_POST["SelectedPaySystemId"], //Выбранная платёжная система (1 - идентификатор тестовой платежной системы,
        //необходимо передавать идентификатор системы, которую выберет пользователь)
        "Products" => array( // Список продуктов
            array(
                "ImageUrl" => "http://someImage.com//some.jpg", // Ссылка на изображение товара
                "ProductItemsNum" => "1", // Колличество
                "ProductName" => "Модель танка Т34-85 ", // Наименование товара
                "ProductPrice" => "500", //Стоимость товара
                "ProductId" => "123", // Идентификатор товара из системы мерчанта. Необходим для аналити продаж
            ),
            array(
                "ImageUrl" => "http://someImage.com/some.jpg", // Ссылка на изображение товара
                "ProductItemsNum" => "2", // Колличество
                "ProductName" => "Модель танка Т34-76 ", // Наименование товара
                "ProductPrice" => "400", //Стоимость товара
                "ProductId" => "124", // Идентификатор товара из системы мерчанта. Необходим для аналити продаж
            )
        ),
        "Currency" => "UAH", // Валюта (UAH, USD, RUB, EUR)
        "Language" => "RU", // Язык страницы оплаты (RU, EN)

        "PaymentDetails" => array( //Детали платежа
            //Обязательные поля
            "EMail" => "samplemail@gmail.com", //Емайл клиента
            "PhoneNumber" => "0937777777", //Номер телефона клиента

            "MerchantInternalPaymentId" => "1234", // Номер платежа в системе мерчанта
            "MerchantInternalUserId" => "21", //Номер пользователя в системе мерчанта

            "StatusUrl" => "www.mysite.com/StatusUrl", // url для ответа платежного сервера с состоянием платежа.
            "ReturnUrl" => "www.mysite.com/SuccessPage", //url возврата ползователя после платежа.

            //По возможности нужно заполнить эти поля.
            "CustomMerchantInfo" => "", // Любая информация
            "BuyerCountry" => "Украина", //Страна
            "BuyerFirstname" => "Ярослав", //Имя,
            "BuyerPatronymic" => "Иванов", // отчество
            "BuyerLastname" => "Иванович", //Фамилия
            "BuyerStreet" => "Школьная 34", // Адрес
            "BuyerZone" => "Одесская область", //   Область
            "BuyerZip" => "65000", //  Индекс
            "BuyerCity" => "Ивановка", //   Город,

            //аналогичная информация о доставке. Если информация совпадает можно скопировать.
            "DeliveryFirstname" => "Иван",
            "DeliveryPatronymic" => "Владимирович",
            "DeliveryLastname" => "Петров",
            "DeliveryZip" => "65000",
            "DeliveryCountry" => "Украина",
            "DeliveryStreet" => "Больнична 43а",
            "DeliveryCity" => "Одесса",
            "DeliveryZone" => "Одесса",
        ),
    );

    //Результатом запроса будет код закодированный в base64 для продолжения оплаты, который необходимо вставить в html-код текущей старницы
    ////ExternalForm - код закодированный в base64, который необходимо вставить в html-код текущей старницы
    ////ErrorCode - Код ошибки в системе (0 - успешный запрос)
    ////DebugMessage - Описание ошибки
    $createPaymentResponse = $api->CreatePayment($request);

}
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php if (!empty($merchantInfo)) { ?>
    MerchantInfo<br/>
    ErrorCode: <?php echo $merchantInfo["ErrorCode"] ?><br/>
    DebugMessage: <?php echo $merchantInfo["DebugMessage"] ?><br/>
    <form method="post" action="">
        <input type="hidden" name="action" value="create_payment">
        <select name="SelectedPaySystemId">
            <?php foreach ($merchantInfo["PaySystems"] as $paySystem) { ?>
                <option value="<?php echo $paySystem["Id"] ?>"><?php echo $paySystem["PaySystemName"] ?></option>
            <?php } ?>
        </select>
        <input type="submit" value="Create payment">
    </form>
<?php } ?>

<br/>

<?php if (!empty($createPaymentResponse)) { ?>
    CreatePayment<br/>
    ErrorCode: <?php echo $createPaymentResponse["ErrorCode"] ?><br/>
    DebugMessage: <?php echo $createPaymentResponse["DebugMessage"] ?><br/>
    <?php echo base64_decode($createPaymentResponse["ExternalForm"]) ?>
<?php } ?>

</body>
</html>
