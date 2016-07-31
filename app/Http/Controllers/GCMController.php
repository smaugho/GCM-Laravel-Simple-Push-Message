<?php

namespace App\Http\Controllers;

use App\Gcm;
use Input;
use App\Http\Controllers\Controller;

class GCMController extends Controller
{

    public function registerGcm() {
        $userId = Input::get('user_id');
        $registeredId = Input::get('registered_id');

        $gcm = Gcm::where('user_id', $userId)->where('registeredId', $registeredId)->first();
        if (!$gcm) {
            Gcm::create(['user_id' => $userId, 'registeredId' => $registeredId]);
        }

        return ["success" => 1];
    }

}

