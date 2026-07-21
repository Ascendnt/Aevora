<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'email', 'password_hash', 'is_superadmin'];
    protected $useTimestamps = true;

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $builder = $this->where('email', strtolower(trim($email)));

        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }

        return $builder->countAllResults() > 0;
    }

    public function setPassword(int $userId, string $plainPassword): bool
    {
        return $this->update($userId, ['password_hash' => password_hash($plainPassword, PASSWORD_DEFAULT)]);
    }
}
