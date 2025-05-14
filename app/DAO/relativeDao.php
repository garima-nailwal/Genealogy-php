<?php

namespace App\Dao;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;


use App\Models\User;
use App\Models\Relative;     //familyrelatiosnhip


class relativeDao
{

    public function createFamilyRelationship($data)
    {
        try {
            DB::beginTransaction(); 

            // Log the incoming data for debugging purposes
            \Log::info('Incoming Family Relationship Payload:', $data);

            $familyRelationship = new Relative();

            // Try to assign the incoming data to the model attributes with error handling for each field
            try { $familyRelationship->primary_user_id = $data['primary_user_id']; } catch (Exception $e) { throw new Exception('Failed at primary_user_id: ' . $e->getMessage()); }
            try { $familyRelationship->relative_id = $data['relative_id']; } catch (Exception $e) { throw new Exception('Failed at relative_id: ' . $e->getMessage()); }
            try { $familyRelationship->relationship_id = $data['relationship_id']; } catch (Exception $e) { throw new Exception('Failed at relationship_id: ' . $e->getMessage()); }

            // Save the family relationship record
            if (!$familyRelationship->save()) {
                throw new Exception('Family Relationship save() returned false.');
            }

            // Commit the transaction if everything is successful
            DB::commit();

            // Return the saved family relationship
            return $familyRelationship;

        } catch (Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            // Log the error for debugging
            \Log::error('Create Family Relationship Failed at Specific Stage: ' . $e->getMessage());
            return false; // Return false if the operation fails
        }
    }
}