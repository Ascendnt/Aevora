<?php

namespace App\Models;

use CodeIgniter\Model;

class PositionModel extends Model
{
    protected $table         = 'positions';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['company_id', 'department_id', 'title'];
    protected $useTimestamps = true;
    protected $validationRules = ['title' => 'required|min_length[2]|max_length[190]'];
}