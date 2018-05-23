<?php
// подготавливаем переменные для отправки данных по факту добавления записи фактически произведенной транзакции
$userId = '345';  # внутренний ID пользователя, присвоенный при регистрации в бэкенде
$currency = 'ETH';  # выбранная Валюта транзакции
$investments_count = 0.01; # Сумма инвестирования в тарнзакции
$total_tokens =  '1.1 ZVR'; # Общая сумма токенов в тарнзакции
$total = preg_replace( '/\sZVR/', '', $total_tokens);  // Общая сумма токенов в тарнзакции - только число (number)
$clientId = '1958068311.1523712548';  # clientID - неперсонализированный идентификатор пользователя в GA - берем его в фронтенде - он должен ТОЧНО соответствовать пользователю и его userId


// собираем данные, который будем отправлять в GA в массив
$data = array(
'v' => 1, # обязательный неизменный параметр версия аналитики
'tid' => 'UA-111018324-1', # id аналитики, в продакшн изменяем для каждого сайта
'cid' => $clientId,
't' => 'event', # обязательный пнеизменный параметр - тип хита
'ec' => 'transferFunds',  # постоянная величина название категории события
'ea' => 'user'.$userId.' '.$investments_count.' '.$currency, #изменяемая величина - название действия события
'el' => 'total '.$total_tokens, # изменяемая величина - название метки события
'ev' => round($total), # изменяемая величина - Целое число (number) сумма токенов - величина ценности события
'cd1' => $clientId,
'dp' => '/transfer'  # обязательный постоянный параметр - виртуальная страница
);

//$url = 'https://www.google-analytics.com/debug/collect';   # тестирование отправки данных - возвращает сообщение о результате
$url = 'https://www.google-analytics.com/collect';
$content = http_build_query($data);
$content = utf8_encode($content);

// отправка массива с указанными выше параметрами и массивом данных
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type: application/x-www-form-urlencoded'));
curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
curl_setopt($ch,CURLOPT_POST, TRUE);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);  # убирает вывод данных в браузер о результате отправки хита, при отладке убираем
curl_setopt($ch,CURLOPT_POSTFIELDS, $content);
curl_exec($ch);

// Проверяем статус HTTP - вернул ли зарос код 200
if (!curl_errno($ch)) {
  switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
    case 200:  # OK
      echo 'Status of user and transaction updated';  # в продакшн здесь добавляем в запись в БД метку что данные в GA отправлены
      break;
    default:
      echo 'Unexpected HTTP code: ', $http_code, "\n"; # в продакшн здесь добавляем в запись в БД метку что данные в GA не отправлены и возвращенный код HTTP
  } 
} else {
    echo 'Curl error number '.curl_errno($ch); # в продакшн здесь добавляем в запись в БД метку что данные в GA не отправлены и возвращенный код ошибки
}

curl_close($ch);
?>