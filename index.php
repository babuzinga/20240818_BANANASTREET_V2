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
  $data = file_get_contents_parser_array($url, '#<script>window.__remixContext = (.+?);</script>#is');

  $data = $data['state'] ?? $data;
  $data = $data['loaderData'] ?? $data;
  $data = $data['routes/$slug+/_index/route'] ?? $data;
  $data = $data['data'] ?? $data;
  $data = $data['tracks'] ?? $data;
  $data = $data['nodes'] ?? $data;

  if (!empty($data)) {
    echo '<ul>';
    foreach ($data as $key => $file) {
      echo "<li><a href='{$file['file']['url']}'>" . number_track($key + 1) . ". {$file['title']}</a></li>";
    }
    echo '</ul>';
  }
}
?>
</body>
</html>
