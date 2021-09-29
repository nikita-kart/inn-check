<?php

include "./database.php";

function check(){
    if (isset($_POST['inn'])){
        $inn_string = $_POST['inn'];

        //проверка ввода
        //состоит ли строка исключительно из цифр?
        if (!ctype_digit($inn_string)){
            return "ИНН должен состоять только из цифр.";
        }

        //проверка правильности инн (https://www.egrul.ru/test_inn.html)
        $length = strlen($inn_string);
        $arr = str_split($inn_string);
        if ($length == 10){
            $coeffs = array(2,4,10,3,5,9,4,6,8,0);
            $sum = 0;
            for ($i = 0; $i < 10; $i++){
                $sum += $arr[$i] * $coeffs[$i];
            }
            $control = $sum % 11;
            if ($control > 9) $control = $control % 10;
            if ($control != $arr[9]) return "ИНН введён некорректно или с ошибками.";
        }
        else if ($length == 12){
            //контрольное число 1
            $coeffs_1 = array(7,2,4,10,3,5,9,4,6,8,0);
            $sum_1 = 0;
            for ($i = 0; $i < 11; $i++){
                $sum_1 += $arr[$i] * $coeffs_1[$i];
            }
            $control_1 = $sum_1 % 11;
            if ($control_1 > 9) $control_1 = $control_1 % 10;
            if ($control_1 != $arr[10]) return "ИНН введён некорректно или с ошибками.";
            //контрольное число 2
            $coeffs_2 = array(3,7,2,4,10,3,5,9,4,6,8,0);
            $sum_2 = 0;
            for ($i = 0; $i < 12; $i++){
                $sum_2 += $arr[$i] * $coeffs_2[$i];
            }
            $control_2 = $sum_2 % 11;
            if ($control_2 > 9) $control_2 = $control_2 % 10;
            if ($control_2 != $arr[11]) return "ИНН введён некорректно или с ошибками.";
        }
        else{
            return "ИНН должен состоять из 10 или 12 цифр.";
        }

        //проверяем в базе данных
        $inn_info = db_get_inn_info($inn_string);
        if ($inn_info !== false){
            //echo "В базе данных есть подходящая запись\n";
            return $inn_info;   //если запись есть и она не устарела, то возвращаем её сообщение
        }
        else{
            //echo "В базе данных нет подходящей записи\n";
        }

        $request = form_json($inn_string);
        //$request = array("inn" => $inn_string, "requestDate" => date("Y-m-d"));
        $response = call_api($request);
        
        $message = decode_result($response);
        db_add_inn_info($inn_string, $message, date("Y-m-d"));
        return $message;
    }
    else{
        return "Заполните поле для проверки ИНН. Здесь будет выведен результат проверки.";
    }
}

function form_json($inn){
    $arr = array("inn" => $inn, "requestDate" => date("Y-m-d"));
    //echo json_encode($arr);
    return json_encode($arr);
}

function call_api($request){
    $url = "https://statusnpd.nalog.ru/api/v1/tracker/taxpayer_status";
    // $curl = curl_init();
    // curl_setopt($curl, CURLOPT_URL, $url);
    // curl_setopt($curl, CURLOPT_POST, true);
    // curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
    // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    // $response = curl_exec($curl);
    // curl_close($curl);
    //через curl не получилось. в ответе было сообщение "Внутренняя ошибка. Повторите ещё раз позже."
    //решение ниже я нагуглил
    $options = array(
        "http" => array(
            "method"  => "POST",
            "header"  => array(
                "Content-type: application/json",
            ),
            "content" => $request
        ),
    );
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    //echo $response;
    return json_decode($response, true);
}

function decode_result($response_array) : string{
    if (isset($response["code"])){
        return "Произошла ошибка. Код: " . $response_array["code"] . " " . $response_array["message"];
    }
    return $response_array["message"];
}

?>