<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 *
 */
class AppDashboardModel extends NajModel {

    private $userId;

    protected function loadTable() {
        $this->setTable('dashboard');
        $this->addColumn('id', true);
    }

    public function dashboard() {
        $chatInfo = DB::table('chat_rel_usuarios')
            ->where('id_usuario', $this->getUserId())
            ->first();

        return [
            'chat_info' => $chatInfo,
        ];
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

}
