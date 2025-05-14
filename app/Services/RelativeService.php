<?php

namespace App\Services;

use App\Dao\relativeDao;

class RelativeService
{

    protected $relativeDao;

    // Constructor to inject the DAO
    public function __construct(relativeDao $familyRelationshipDAO)
    {
        $this->relativeDao = $familyRelationshipDAO;
    }

    public function manageFamilyRelationship($data)
    {
        $result = $this->relativeDao->createFamilyRelationship($data);
        
        if ($result) {
            return ['data' => $result, 'msgArr' => [], 'status' => 'success'];
        }

        return ['data' => [], 'msgArr' => ['Unable to create family relationship'], 'status' => 'failed'];
    }
}