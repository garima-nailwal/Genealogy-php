<?php

namespace App\Dao;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


use App\Models\User;
use App\Models\Relationship;
use App\Models\State;
use App\Models\District;
use App\Models\Occupation;
use App\Models\Caste;
use App\Models\MaritialStatus;
use App\Models\Religion;


class userDao
{
    public function fetchUser($id)
    {
        $user = User::where('user_id', $id)->first();
        return $user;
    }

    public function getAllUsers()
    {
        return User::all()->toArray();
    }

    public function fetchMasterData()
    {
        return [
            'relationships' => relationship::all()->toArray(),
            'states' => state::all()->toArray(),
            'districts' => district::all()->toArray(),
            'occupations' => occupation::all()->toArray(),
            'castes' => caste::all()->toArray(),
            'maritial_statuses' => maritialStatus::all()->toArray(),
            'religions' => religion::all()->toArray()
        ];

    }

    public function fetchDistrictsByState($stateId)
    {
        $data = DB::table('districts')
                 ->select('id', 'district')
                 ->where('state_id', $stateId)
                 ->get();
    
        return ['data' => $data];
    }

public function fetchrelationships()
{
    $data = DB::table('relationship')
              ->select('id', 'relationship_type')
              ->get();

    return ['data' => $data];
}

public function fetchoccupation()
{
    $data = DB::table('occupation')
              ->select('id', 'occupation_name')
              ->get();

    return ['data' => $data];
}

public function fetchmaritialstatus()
{
    $data = DB::table('maritial_status')
              ->select('id', 'maritial_status')
              ->get();

    return ['data' => $data];
}

public function fetchcaste()
{
    $data = DB::table('castes')
              ->select('id', 'caste_name')
              ->get();

    return ['data' => $data];
}

public function fetchstate()
{
    $data = DB::table('state')
              ->select('id', 'state')
              ->get();

    return ['data' => $data];
}

public function fetchreligion()
{
    $data = DB::table('religions')
              ->select('id', 'religion_name')
              ->get();

    return ['data' => $data];
}

    public function createUser($data)
    {
        try {
            DB::beginTransaction();
    
            \Log::info('Incoming User Payload:', $data);
    
            $user = new User();
    
            try { $user->first_name = $data['first_name']; } catch (\Exception $e) { throw new \Exception('Failed at first_name: ' . $e->getMessage()); }
            try { $user->middle_name = $data['middle_name'] ?? null; } catch (\Exception $e) { throw new \Exception('Failed at middle_name: ' . $e->getMessage()); }
            try { $user->last_name = $data['last_name']; } catch (\Exception $e) { throw new \Exception('Failed at last_name: ' . $e->getMessage()); }
            try { $user->birth_date = $data['birth_date'] ?? null; } catch (\Exception $e) { throw new \Exception('Failed at birth_date: ' . $e->getMessage()); }
            try { $user->gender = $data['gender'] ?? null; } catch (\Exception $e) { throw new \Exception('Failed at gender: ' . $e->getMessage()); }
            try { $user->email = $data['email']; } catch (\Exception $e) { throw new \Exception('Failed at email: ' . $e->getMessage()); }
            try { $user->mobile_number = $data['mobile_number']; } catch (\Exception $e) { throw new \Exception('Failed at mobile_number: ' . $e->getMessage()); }
            try { $user->state_id = $data['state_id'] ?? null; } catch (\Exception $e) { throw new \Exception('Failed at state_id: ' . $e->getMessage()); }
            try { $user->district_id = $data['district_id'] ?? null; } catch (\Exception $e) { throw new \Exception('Failed at district_id: ' . $e->getMessage()); }
            try { $user->occupation_id = $data['occupation_id'] ?? null; } catch (\Exception $e) { throw new \Exception('Failed at occupation_id: ' . $e->getMessage()); }
            try { $user->maritial_status = $data['maritial_status'] ?? null; } catch (\Exception $e) { throw new \Exception('Failed at maritial_status: ' . $e->getMessage()); }
            try { $user->religion_id = $data['religion_id'] ?? null; } catch (\Exception $e) { throw new \Exception('Failed at religion_id: ' . $e->getMessage()); }
            try { $user->caste_id = $data['caste_id'] ?? null; } catch (\Exception $e) { throw new \Exception('Failed at caste_id: ' . $e->getMessage()); }
            try { $user->permanent_address = $data['permanent_address'] ?? null; } catch (\Exception $e) { throw new \Exception('Failed at permanent_address: ' . $e->getMessage()); }
            try { $user->city = $data['city'] ?? null; } catch (\Exception $e) { throw new \Exception('Failed at city: ' . $e->getMessage()); }
            try { $user->pincode = $data['pincode'] ?? null; } catch (\Exception $e) { throw new \Exception('Failed at pincode: ' . $e->getMessage()); }
    
            if (!$user->save()) {
                throw new \Exception('User save() returned false.');
            }
    
            DB::commit();
            return $user;
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Create User Failed at Specific Stage: ' . $e->getMessage());
            return false;
        }
    }

    public function updateUser($data)
    {
        try {
            DB::beginTransaction();

            $user = User::find($data['user_id']);
            if (!$user) {
                return false;
            }

            $user->first_name = $data['first_name'] ?? $user->first_name;
            $user->middle_name = $data['middle_name'] ?? $user->middle_name;
            $user->last_name = $data['last_name'] ?? $user->last_name;
            $user->birth_date= $data['birth_date'] ?? $user->birth_date;

            $user->gender = $data['gender'] ?? $user->gender;
            $user->email = $data['email'] ?? $user->email;
            $user->mobile_number = $data['mobile_number'] ?? $user->mobile_number;
            $user->state_id = $data['state_id'] ?? null;
            $user->district_id = $data['district_id'] ?? null;
            $user->occupation_id = $data['occupation_id'] ?? null;
            $user->relationship_id = $data['relationship_id'] ?? null;
            $user->maritial_status = $data['maritial_status'] ?? null;
            
            $user->religion_id = $data['religion_id'] ?? null;
            $user->caste_id = $data['caste_id'] ?? null;
            $user->permanent_address = $data['permanent_address'] ?? null;

            $user->save();

            DB::commit();
            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}


