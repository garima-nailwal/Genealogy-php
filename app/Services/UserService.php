<?php

namespace App\Services;

use App\Dao\userDao;

class UserService
{
    public function getUser($id)
    {
        $user = (new userDao)->fetchUser($id);
        if (!$user) {
            return ['data' => [], 'msgArr' => ['User not found'], 'status' => 'failed'];
        }
        return ['data' => $user, 'msgArr' => [],'status' => 'success'];
    }

    public function manageUser($data)
    {
        $dao = new userDao;
    
        if (!isset($data['user_id'])) {
            $result = $dao->createUser($data); 
        } else {
            $result = $dao->updateUser($data);
        }
    
        if (!$result) {
            return ['data' => [], 'msgArr' => ['Unable to process user data'], 'status' => 'failed'];
        }
    
        return ['data' => $result,'msgArr'=> [], 'status' => 'success'];
    }
    
    public function getAllUsers()
    {
        $users = (new userDao)->getAllUsers();
        if (!$users) {
            return ['data' => [], 'msgArr' => ['No users found'], 'status' => 'failed'];
        }
        return ['data' => $users, 'status' => 'success'];
    }

    public function getMasterData()
    {


        $masterData = (new userDao)->fetchMasterData();

        if (!$masterData) {
            return ['data' => [], 'msgArr' => ['No master data found'], 'status' => 'failed'];
        }
    
        // filter only required columns at the service level
        $filteredData = [
            'relationships' => collect($masterData['relationships'])->map(function ($item) {
                return ['id' => $item['id'], 'relationship_type' => $item['relationship_type']];
            }),
    
            'states' => collect($masterData['states'])->map(function ($item) {
                return ['id' => $item['id'], 'name' => $item['state']];
            }),

            'religions' => collect($masterData['religions'])->map(function ($item) {
                return ['id' => $item['id'], 'name' => $item['religion_name']];
            }),

            'districts' => collect($masterData['districts'])->map(function ($item) {
                return ['id' => $item['id'], 'name' => $item['district']];
            }),
            
            // 'districts' => collect($masterData['districts'])
            // ->when($stateId, function ($collection) use ($stateId) {
            //     return $collection->where('state_id', $stateId);
            // })
            // ->map(function ($item) {
            //     return [
            //         'id' => $item['id'],
            //         'state_id' => $item['state_id'],  // ensure this key matches your DB
            //         'name' => $item['district']
            //     ];
            // }),
    
            'occupations' => collect($masterData['occupations'])->map(function ($item) {
                return ['id' => $item['id'], 'name' => $item['occupation_name']];
            }),
    
            'castes' => collect($masterData['castes'])->map(function ($item) {
                return ['id' => $item['id'], 'name' => $item['caste_name']];
            }),
    
            'maritial_statuses' => collect($masterData['maritial_statuses'])->map(function ($item) {
                return ['id' => $item['id'], 'status' => $item['maritial_status']];
            }),
        ];
    
        return ['data' => $filteredData, 'msgArr'=> [],'status' => 'success'];
    }

    public function getDistrictsByState($stateId)
    {
        return (new UserDao)->fetchDistrictsByState($stateId);
    }
    public function getrelationships()
    {
        $relationship = (new userDao)->fetchrelationships();
        return $relationship; 
    }
    public function getoccupation()
    {
        $occupation = (new userDao)->fetchoccupation();
        return $occupation; 
    }

    public function getcaste()
    {
        $caste = (new userDao)->fetchcaste();
        return $caste; 
    }

    public function getmaritialstatus()
    {
        $maritialstatus = (new userDao)->fetchmaritialstatus();
        return $maritialstatus; 
    }

    public function getstate()
    {
        $state = (new userDao)->fetchstate();
        return $state; 
    }

    public function getreligion()
    {
        $religion = (new userDao)->fetchreligion();
        return $religion; 
    }
 }
