<?php

namespace App\Models;

use App\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'status', 'creater_id'];
	
	protected $appends = ['given_permissions'];
	public function getGivenPermissionsAttribute(): array
	{
		$permissions = [];		
		foreach($this->permissions as $permission){
			if($permission->action=="Read"){
				//$permission->action="viewAny";
				$permissions[] = $permission->action="viewAny" . '-' . $permission->model;
			}
			if($permission->action=="Add"){
				//$permission->action="create";
				$permissions[] = $permission->action="create" . '-' . $permission->model;
			}
			if($permission->action=="Update"){
				//$permission->action="updateAny";
				$permissions[] = $permission->action="updateAny" . '-' . $permission->model;
				$permissions[] = $permission->action="update" . '-' . $permission->model;
			}
			if($permission->action=="Delete"){
				//$permission->action="deleteAny";
				$permissions[] = $permission->action="deleteAny" . '-' . $permission->model;
				$permissions[] = $permission->action="delete" . '-' . $permission->model;
				$permissions[] = $permission->action="restoreAny" . '-' . $permission->model;
				$permissions[] = $permission->action="restore" . '-' . $permission->model;
				$permissions[] = $permission->action="forceDeleteAny" . '-' . $permission->model;
				$permissions[] = $permission->action="forceDelete" . '-' . $permission->model;
			}
			
		}
		return $permissions;
	}
    public function scopeFilter($query, array $filters)
	{
		$query->when($filters['search'] ?? null, function ($query, $search) {
			$query->where(function ($query) use ($search) {
				$query->where('name', 'like', '%'.$search.'%');
			});
		})->when(!in_array("viewAny-role", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
			$query->where("creater_id", auth()->user()->id);
		});
	}
	public function delete(){
		$this->permissions()->get()->map(function($record){
			$record->delete();
		});
		parent::delete();
	}
	/**
	 * The users that belong to the role.
	 */
	public function users()
	{
		return $this->belongsToMany(User::class);
	}

	
	/**
	 * The permissions that belong to the role.
	 */
    public function permissions()
    {
        return $this->morphMany(Permission::class, 'permissionable');
    }
}
