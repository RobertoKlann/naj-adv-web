<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\NajController;
use App\Models\AppChatModel;

class AppDeviceController extends NajController
{
  public function onLoad()
  {
    $this->setModel(new AppChatModel);
  }
}
