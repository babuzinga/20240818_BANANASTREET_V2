<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>

<style>
  * {
    font-family: "Courier New";
    font-size: 14px;
  }
  li {
    margin: 6px 0;
  }
</style>
<body>

<form action="/" method="get">
  <input type="text" name="l" value="<?= $_GET['l'] ?? '' ?>" style="width: 500px">
  <button type="submit">Parse</button>
</form>

<?php

require_once ('helpers.php');

if (isset($_GET['l'])) {
  $url = $_GET['l'];
  $parse = $parse_tmp = file_get_contents_parser_array($url, '#<script>window.__remixContext.streamController.enqueue\((.+?)\);</script>#is');

  /*$data = $data['state'] ?? $data;
  $data = $data['loaderData'] ?? $data;
  $data = $data['routes/$slug+/_index/route'] ?? $data;
  $data = $data['data'] ?? $data;
  $data = $data['tracks'] ?? $data;
  $data = $data['nodes'] ?? $data;*/

  $data = [];
  if (!empty($parse)) {
    foreach ($parse as $item) {
      if (!is_array($item) && str_contains($item, '.mp3')) {
        $path = explode('/', $item);
        $track_id = $path[count($path) - 2];
        $track_title = false;

        reset($parse_tmp);
        foreach ($parse_tmp as $key => $tmp) {
          if (empty($track_title) && (int)$tmp === (int)$track_id) {
            //echo (int)$tmp . ' ' . (int)$track_id . ' ' . $key . '<br>';
            $track_title = $parse_tmp[$key + 1];
            break;
          }
        }

        $data[$track_id] = [
            'id' => $track_id,
            'url' => $item,
            'title' => $track_title ?? '???',
        ];
      }
    }
  }

  if (!empty($data)) {
    $i = 0;
    echo '<ul>';
    foreach ($data as $key => $file) {
      echo "<li><a href='{$file['url']}'>" . number_track(++$i) . ". {$file['title']}</a></li>";
    }
    echo '</ul>';
  }
}
?>
</body>
</html>
