<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table         = 'departments';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['company_id', 'name'];
    protected $useTimestamps = true;
    protected $validationRules = ['name' => 'required|min_length[2]|max_length[190]'];
}