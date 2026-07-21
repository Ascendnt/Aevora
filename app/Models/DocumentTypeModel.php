<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * document_types is a small, mostly-static lookup table (employment_contract,
 * nda, offer_letter, coe, quitclaim, bir_2316, nte_nod) seeded by migration.
 * This model exists so other code can look types up by id or slug key rather
 * than hard-coding either.
 */
class DocumentTypeModel extends Model
{
    protected $table         = 'document_types';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['key', 'name', 'requires_signature'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'key'  => 'required|max_length[60]',
        'name' => 'required|max_length[190]',
    ];

    public function findByKey(string $key): ?array
    {
        return $this->where('key', $key)->first();
    }
}
