<?php
namespace App\Models;

use App\Core\DB;
use PDO;

abstract class BaseModel {
    protected PDO $db;
    public function __construct() {
        $this->db = DB::conn();
    }
}