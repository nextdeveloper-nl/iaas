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
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }
    
    public function slug($value)
    {
        return $this->builder->where('slug', 'like', '%' . $value . '%');
    }
    
    public function description($value)
    {
        return $this->builder->where('description', 'like', '%' . $value . '%');
    }
    
    public function geoLatitude($value)
    {
        return $this->builder->where('geo_latitude', 'like', '%' . $value . '%');
    }
    
    public function geoLongitude($value)
    {
        return $this->builder->where('geo_longitude', 'like', '%' . $value . '%');
    }
    
    public function powerSource($value)
    {
        return $this->builder->where('power_source', 'like', '%' . $value . '%');
    }
    
    public function ups($value)
    {
        return $this->builder->where('ups', 'like', '%' . $value . '%');
    }
    
    public function cooling($value)
    {
        return $this->builder->where('cooling', 'like', '%' . $value . '%');
    }
    
    public function cityName($value)
    {
        return $this->builder->where('city_name', 'like', '%' . $value . '%');
    }
    
    public function countryName($value)
    {
        return $this->builder->where('country_name', 'like', '%' . $value . '%');
    }
    
    public function datacenterMaintainer($value)
    {
        return $this->builder->where('datacenter_maintainer', 'like', '%' . $value . '%');
    }
    
    public function maintainer($value)
    {
        return $this->builder->where('maintainer', 'like', '%' . $value . '%');
    }
    
    public function responsible($value)
    {
        return $this->builder->where('responsible', 'like', '%' . $value . '%');
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

    public function isPublic($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_public', $value);
    }

    public function isActive($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_active', $value);
    }

    public function isInMaintenance($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_in_maintenance', $value);
    }

    public function isCarrierNeutral($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_carrier_neutral', $value);
    }

    public function createdAtStart($date)
    {
        return $this->builder->where('created_at', '>=', $date);
    }

    public function createdAtEnd($date)
    {
        return $this->builder->where('created_at', '<=', $date);
    }

    public function updatedAtStart($date)
    {
        return $this->builder->where('updated_at', '>=', $date);
    }

    public function updatedAtEnd($date)
    {
        return $this->builder->where('updated_at', '<=', $date);
    }

    public function deletedAtStart($date)
    {
        return $this->builder->where('deleted_at', '>=', $date);
    }

    public function deletedAtEnd($date)
    {
        return $this->builder->where('deleted_at', '<=', $date);
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
