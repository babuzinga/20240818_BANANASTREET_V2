<?php

function number_track(string $n): string
{
  return strlen($n) === 1 ? '0' . $n : $n;
}

/**
 * Получение содержимого страницы + поиск части кода по регулярному выражению + преобразование в массив
 * @param string $url
 * @param string $preg
 * @return array
 */
function file_get_contents_parser_array(string $url, string $preg): array
{
  // file_get_contents - очень долго ждет ответа, если ресурс недоступен
  $content = curl_request($url);

  preg_match($preg, $content, $match);

  $data = !empty($match[1]) ? str_replace(["\r\n", "\r", "\n"], "", $match[1]) : '';
  $data = str_replace(['\"'], '"', $data);
  $data = str_replace(['\n"'], '', $data);
  $data = str_replace(['"[{'], '[{', $data);

  $data = json_decode($data, true);

  return !empty($data) && is_array($data) ? $data : [];
}

/**
 * @param $array
 * @param bool $stop
 */
function print_array($array, bool $stop = false): void
{
  echo '<pre>', print_r($array, 1), '</pre>';
  if ($stop) exit;
}

/**
 * @param string $url
 * @param array $fields
 * @param string $method
 * @param array $headers
 * @param bool $return_only_content
 * @return mixed
 */
function curl_request(string $url, array $fields = [], string $method = 'get', array $headers = [], bool $return_only_content = true): mixed
{
  if (empty($url)) return false;
  if (empty($headers)) $headers = [
    "Content-Type: application/x-www-form-urlencoded",
    "cache-control: no-cache"
  ];

  $options = [
    CURLOPT_RETURNTRANSFER  => true,        // return web page
    CURLOPT_HEADER          => false,       // don't return headers
    CURLOPT_FOLLOWLOCATION  => true,        // follow redirects
    CURLOPT_ENCODING        => "",          // handle all encodings
    CURLOPT_USERAGENT       => $_SERVER['HTTP_USER_AGENT'], // who am i - $user_agent
    CURLOPT_AUTOREFERER     => true,        // set referer on redirect
    CURLOPT_CONNECTTIMEOUT  => 5,           // timeout on connect - Время установления tcp-соединения при попытке подключения. Используйте 0 для ожидания бесконечно.
    CURLOPT_TIMEOUT         => 10,          // timeout on response - Время установления соединения + время отправки запроса + время ответа до его получения.
    CURLOPT_MAXREDIRS       => 3,           // stop after 3 redirects
    CURLOPT_SSL_VERIFYPEER  => false,       // Disabled SSL Cert checks
    //CURLOPT_FAILONERROR     => true,        // true для подробного отчёта при неудаче, если полученный HTTP-код больше или равен 400
    CURLOPT_HTTPHEADER      => $headers,    // Content-Type: application/json
  ];

  if (in_array($method, ['put', 'post', 'delete'])) {
    $options[CURLOPT_POST] = true;
    $options[CURLOPT_POSTFIELDS] = ( !empty($fields) && is_array($fields) ) ? json_encode($fields) : '';
  }
  if (in_array($method, ['put', 'delete'])) {
    $options[CURLOPT_CUSTOMREQUEST] = mb_strtoupper($method);
  }
  if ($method == 'get') {
    $url .= ( !empty($fields) && is_array($fields) ) ? ('?' . http_build_query($fields)) : '';
  }

  $ch = curl_init($url);
  curl_setopt_array($ch, $options);
  $content  = curl_exec($ch);
  $err      = curl_errno($ch);
  $errmsg   = curl_error($ch);
  $header   = curl_getinfo($ch);
  curl_close($ch);

  $header['errno'] = $err;
  $header['errmsg'] = $errmsg;
  $header['content'] = $content;

  return $return_only_content ? $content : $header;
}