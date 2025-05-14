<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Models\User;
use App\Models\State;
use App\Models\Relationship;
use App\DAO\userDao;
use Js;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;

    }
    // GET /api/user/{id}
    
    public function getUser($id)
    {
        try {
            $result = $this->userService->getUser(['user_id' => $id]);
            if ($result) {
                return response()->json(['data' => $result['data'],'msgArr' => $result['msgArr'], 'status' => 'success']);
            } else {
                return response()->json(['data' => [], 'msgArr' => ['User not found at controller'], 'status' => 'failed']);
            }
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'msgArr' => [$e->getMessage()], 'status' => 'exception']);
        }
    }

    public function postUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'middle_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            // 'email' => 'required|email',
            // 'mobile_number' => 'required|digits:10',
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

            // If we're adding a family member (add-member), return the user_id
        if (isset($requestData['primary_user_id']) && $requestData['primary_user_id'] != null) {
            // Create the new family member (user) 
            $result = $this->userService->manageUser($requestData);
            if ($result['status'] === 'success') {
                if (isset($result['data']['id'])) {
                return response()->json([
                    'data' => ['user_id' => $result['data']['id']],  // Return user_id of the newly created user
                    'msgArr' => [],
                    'status' => 'success'
                ]);
            }else {
                return response()->json([
                    'data' => [],
                    'msgArr' => ['Unable to find created user ID'],
                    'status' => 'failed'
                ]);
            }
            } else {
                return response()->json([
                    'data' => [],
                    'msgArr' => $result['msgArr'],
                    'status' => 'failed'
                ]);
            }
        } else {
            $result = $this->userService->manageUser($requestData);
    
            if ($result['status'] === 'success') {
                return response()->json(['data' => $result['data'],'msgArr' => [] , 'status' => 'success']);
            } else {
                return response()->json(['data' => [],'msgArr' => $result['msgArr'] , 'status' => 'failed']);
            }
        }
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'msgArr' => [$e->getMessage()], 'status' => 'exception']);
        }
    }

    public function getAllUsers()
    {
        try {
            $users = $this->userService->getAllUsers();
            if ($users && count($users) > 0) {
                return response()->json(['data' => $users['data'], 'status' => 'success']);
            } else {
                return response()->json(['data' => [], 'msgArr' => ['No users found'], 'status' => 'failed']);
            }
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'msgArr' => [$e->getMessage()], 'status' => 'exception']);
        }
    }

    // GET /api/master-data
    public function getMasterData()
    {
        try {
            // $stateId = $request->query('state_id'); // optional
            // $result = $this->userService->getMasterData($request);
            $result = $this->userService->getMasterData();
            // if ($result['status'] === 'success') {

            return response()->json(['data' => $result['data'],'msgArr' => [], 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'msgArr' => [$e->getMessage()], 'status' => 'exception']);
        }
    }


public function getDropdownData($key,Request $request)
{
    try {
        switch ($key) {
            case 'relationship':
                $result = $this->userService->getrelationships();
                break;
            case 'occupation':
                $result = $this->userService->getoccupation();
                break;
            case 'maritialstatus':
                $result = $this->userService->getmaritialstatus();
                break;
            case 'caste':
                $result = $this->userService->getcaste();
                break;
            case 'state':
                $result = $this->userService->getstate();
                break;
            case 'district':
                $stateId = $request->query('state_id');
                if (!$stateId) {
                    return response()->json([
                        'status' => 'error',
                        'msgArr' => ['state_id is required for districts'],
                    ], 400);
                }
                $result = $this->userService->getDistrictsByState($stateId);
                break;
            case 'religion':
                $result = $this->userService->getreligion();
                break;
            default:
                return response()->json([
                    'status' => 'error',
                    'msgArr' => ['Invalid dropdown key'],
                ], 400);
        }

        return response()->json([
            'data' => $result['data'],
            'msgArr' => [],
            'status' => 'success'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'data' => [],
            'msgArr' => [$e->getMessage()],
            'status' => 'exception'
        ]);
    }
}
}