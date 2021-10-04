<?php

  header('Content-Type: application/json');

//  usleep(40000);

  echo json_encode([
    'username' => 'johndoe',
    'address' => [
      'city' => 'Venlo',
      'zip' => '5911KH'
    ]
  ]);
