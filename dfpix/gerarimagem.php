<?php

$data = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);

header("Content-type: image/png");

echo base64_decode($data);

echo $data;

