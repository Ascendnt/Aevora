<?php

namespace App\Models;

use CodeIgniter\Model;

class AccessProfileModel extends Model
{
    protected $table         = 'access_profiles';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
    ];

    public function moduleKeys(int $profileId): array
    {
        return array_column(
            $this->db->table('access_profile_modules')->select('module_key')->where('access_profile_id', $profileId)->get()->getResultArray(),
            'module_key',
        );
    }

    public function setModules(int $profileId, array $moduleKeys): void
    {
        $this->db->table('access_profile_modules')->where('access_profile_id', $profileId)->delete();

        $rows = array_map(static fn ($key) => ['access_profile_id' => $profileId, 'module_key' => $key], $moduleKeys);

        if ($rows !== []) {
            $this->db->table('access_profile_modules')->insertBatch($rows);
        }
    }

    /** All profiles with their module keys attached, for list views. */
    public function allWithModules(): array
    {
        $profiles = $this->orderBy('name')->findAll();

        foreach ($profiles as &$profile) {
            $profile['modules'] = $this->moduleKeys((int) $profile['id']);
        }
        unset($profile);

        return $profiles;
    }

    public function nameExists(string $name, ?int $exceptId = null): bool
    {
        $builder = $this->where('name', $name);

        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }

        return $builder->countAllResults() > 0;
    }
}
