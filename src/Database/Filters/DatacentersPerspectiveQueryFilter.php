<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
        

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class DatacentersPerspectiveQueryFilter extends AbstractQueryFilter
{
    /**
     * Filter by tags
     *
     * @param  $values
     * @return Builder
     */
    public function tags($values)
    {
        $tags = explode(',', $values);

        $search = '';

        for($i = 0; $i < count($tags); $i++) {
            $search .= "'" . trim($tags[$i]) . "',";
        }

        $search = substr($search, 0, -1);

        return $this->builder->whereRaw('tags @> ARRAY[' . $search . ']');
    }

    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }

        
    public function slug($value)
    {
        return $this->builder->where('slug', 'ilike', '%' . $value . '%');
    }

        
    public function description($value)
    {
        return $this->builder->where('description', 'ilike', '%' . $value . '%');
    }

        
    public function geoLatitude($value)
    {
        return $this->builder->where('geo_latitude', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of geoLatitude
    public function geo_latitude($value)
    {
        return $this->geoLatitude($value);
    }
        
    public function geoLongitude($value)
    {
        return $this->builder->where('geo_longitude', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of geoLongitude
    public function geo_longitude($value)
    {
        return $this->geoLongitude($value);
    }
        
    public function powerSource($value)
    {
        return $this->builder->where('power_source', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of powerSource
    public function power_source($value)
    {
        return $this->powerSource($value);
    }
        
    public function ups($value)
    {
        return $this->builder->where('ups', 'ilike', '%' . $value . '%');
    }

        
    public function cooling($value)
    {
        return $this->builder->where('cooling', 'ilike', '%' . $value . '%');
    }

        
    public function cityName($value)
    {
        return $this->builder->where('city_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of cityName
    public function city_name($value)
    {
        return $this->cityName($value);
    }
        
    public function countryName($value)
    {
        return $this->builder->where('country_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of countryName
    public function country_name($value)
    {
        return $this->countryName($value);
    }
        
    public function datacenterMaintainer($value)
    {
        return $this->builder->where('datacenter_maintainer', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of datacenterMaintainer
    public function datacenter_maintainer($value)
    {
        return $this->datacenterMaintainer($value);
    }
        
    public function maintainer($value)
    {
        return $this->builder->where('maintainer', 'ilike', '%' . $value . '%');
    }

        
    public function responsible($value)
    {
        return $this->builder->where('responsible', 'ilike', '%' . $value . '%');
    }

    
    public function tierLevel($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('tier_level', $operator, $value);
    }

        //  This is an alias function of tierLevel
    public function tier_level($value)
    {
        return $this->tierLevel($value);
    }
    
    public function cloudNodesCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('cloud_nodes_count', $operator, $value);
    }

        //  This is an alias function of cloudNodesCount
    public function cloud_nodes_count($value)
    {
        return $this->cloudNodesCount($value);
    }
    
    public function computePoolsCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('compute_pools_count', $operator, $value);
    }

        //  This is an alias function of computePoolsCount
    public function compute_pools_count($value)
    {
        return $this->computePoolsCount($value);
    }
    
    public function storagePoolsCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('storage_pools_count', $operator, $value);
    }

        //  This is an alias function of storagePoolsCount
    public function storage_pools_count($value)
    {
        return $this->storagePoolsCount($value);
    }
    
    public function networkPoolsCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('network_pools_count', $operator, $value);
    }

        //  This is an alias function of networkPoolsCount
    public function network_pools_count($value)
    {
        return $this->networkPoolsCount($value);
    }
    
    public function isPublic($value)
    {
        return $this->builder->where('is_public', $value);
    }

        //  This is an alias function of isPublic
    public function is_public($value)
    {
        return $this->isPublic($value);
    }
     
    public function isActive($value)
    {
        return $this->builder->where('is_active', $value);
    }

        //  This is an alias function of isActive
    public function is_active($value)
    {
        return $this->isActive($value);
    }
     
    public function isInMaintenance($value)
    {
        return $this->builder->where('is_in_maintenance', $value);
    }

        //  This is an alias function of isInMaintenance
    public function is_in_maintenance($value)
    {
        return $this->isInMaintenance($value);
    }
     
    public function isCarrierNeutral($value)
    {
        return $this->builder->where('is_carrier_neutral', $value);
    }

        //  This is an alias function of isCarrierNeutral
    public function is_carrier_neutral($value)
    {
        return $this->isCarrierNeutral($value);
    }
     
    public function createdAtStart($date)
    {
        return $this->builder->where('created_at', '>=', $date);
    }

    public function createdAtEnd($date)
    {
        return $this->builder->where('created_at', '<=', $date);
    }

    //  This is an alias function of createdAt
    public function created_at_start($value)
    {
        return $this->createdAtStart($value);
    }

    //  This is an alias function of createdAt
    public function created_at_end($value)
    {
        return $this->createdAtEnd($value);
    }

    public function updatedAtStart($date)
    {
        return $this->builder->where('updated_at', '>=', $date);
    }

    public function updatedAtEnd($date)
    {
        return $this->builder->where('updated_at', '<=', $date);
    }

    //  This is an alias function of updatedAt
    public function updated_at_start($value)
    {
        return $this->updatedAtStart($value);
    }

    //  This is an alias function of updatedAt
    public function updated_at_end($value)
    {
        return $this->updatedAtEnd($value);
    }

    public function deletedAtStart($date)
    {
        return $this->builder->where('deleted_at', '>=', $date);
    }

    public function deletedAtEnd($date)
    {
        return $this->builder->where('deleted_at', '<=', $date);
    }

    //  This is an alias function of deletedAt
    public function deleted_at_start($value)
    {
        return $this->deletedAtStart($value);
    }

    //  This is an alias function of deletedAt
    public function deleted_at_end($value)
    {
        return $this->deletedAtEnd($value);
    }

    public function iamUserId($value)
    {
            $iamUser = \NextDeveloper\IAM\Database\Models\Users::where('uuid', $value)->first();

        if($iamUser) {
            return $this->builder->where('iam_user_id', '=', $iamUser->id);
        }
    }

    
    public function iamAccountId($value)
    {
            $iamAccount = \NextDeveloper\IAM\Database\Models\Accounts::where('uuid', $value)->first();

        if($iamAccount) {
            return $this->builder->where('iam_account_id', '=', $iamAccount->id);
        }
    }

    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


































}
