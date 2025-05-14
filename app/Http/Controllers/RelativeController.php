<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RelativeService;
use Illuminate\Support\Facades\Validator;

class RelativeController extends Controller
{
    protected $RelativeService;

    public function __construct(RelativeService $RelativeService)
    {
        $this->RelativeService = $RelativeService;
    }

    public function postFamilyRelation(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'primary_user_id' => 'required|exists:users_registration,user_id',  // Ensure the primary user exists in the 'users' table
            'relative_id' => 'required|exists:users_registration,user_id', // Ensure the relative user exists
            'relationship_id' => 'required|exists:relationship,id', // Ensure relationship type exists
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'data' => [],
                'msgArr' => $validator->errors()->all(),
                'status' => 'validation_failed'
            ]);
        }
    
        try {
            
            $requestData = $request->all();
            
            // Call the service to handle the business logic
            $result = $this->RelativeService->manageFamilyRelationship($requestData);
    
           
            if ($result['status'] === 'success') {
                return response()->json([
                    'data' => $result['data'],
                    'msgArr' => [],
                    'status' => 'success'
                ]);
            } else {
                return response()->json([
                    'data' => [],
                    'msgArr' => $result['msgArr'],
                    'status' => 'failed'
                ]);
            }
    
        } catch (\Exception $e) {
            // Return the exception message if any error occurs
            return response()->json([
                'data' => [],
                'msgArr' => [$e->getMessage()],
                'status' => 'exception'
            ]);
        }
    }
}
