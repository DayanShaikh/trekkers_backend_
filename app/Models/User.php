<?php

namespace App\Models;

use App\Helper;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable implements MustVerifyEmail
{
	private $seconds = 3600;
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
		'uuid'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
		'last_logged_in_at' => 'datetime',
	];

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = [
		'given_permissions',
		'is_admin',
		'is_client',
		'is_tour_guide',
		'is_travel_admin',
		'is_travel_brand',
	];

	/**
	 * Interact with the user's permissions.
	 *
	 * @return  \Illuminate\Database\Eloquent\Casts\Attribute
	 */
	protected function getGivenPermissionsAttribute()
	{
		$permissions = [];
		foreach($this->roles as $role){
			foreach($role->permissions as $permission){
				if($permission->action=="Read"){
					$permissions[] = $permission->action="viewAny" . '-' . $permission->model;
				}
				if($permission->action=="Add"){
					$permissions[] = $permission->action="create" . '-' . $permission->model;
				}
				if($permission->action=="Update"){
					$permissions[] = $permission->action="updateAny" . '-' . $permission->model;
					$permissions[] = $permission->action="update" . '-' . $permission->model;
				}
				if($permission->action=="Delete"){
					$permissions[] = $permission->action="deleteAny" . '-' . $permission->model;
					$permissions[] = $permission->action="delete" . '-' . $permission->model;
					$permissions[] = $permission->action="restoreAny" . '-' . $permission->model;
					$permissions[] = $permission->action="restore" . '-' . $permission->model;
					$permissions[] = $permission->action="forceDeleteAny" . '-' . $permission->model;
					$permissions[] = $permission->action="forceDelete" . '-' . $permission->model;
				}
			}
		}
		foreach($this->permissions as $permission){
			if($permission->action=="Read"){
				$permissions[] = $permission->action="viewAny" . '-' . $permission->model;
			}
			if($permission->action=="Add"){
				$permissions[] = $permission->action="create" . '-' . $permission->model;
			}
			if($permission->action=="Update"){
				$permissions[] = $permission->action="updateAny" . '-' . $permission->model;
				$permissions[] = $permission->action="update" . '-' . $permission->model;
			}
			if($permission->action=="Delete"){
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
	public function getIsAdminAttribute(){
		return $this->hasRole('Admin');
	}
	public function getIsTourGuideAttribute(){
		return $this->hasRole('Tour Guide');
	}
	public function getIsTravelAdminAttribute(){
		return $this->hasRole('Travel Admin');
	}
	public function getIsTravelBrandAttribute(){
		return $this->hasRole('Travel Brand');
	}
	public function getIsClientAttribute(){
		return $this->hasRole('Client');
	}
	public function hasRole($roleName){
		//return Cache::remember($this->id.'_has_role_'.$roleName, $this->seconds, function () use($roleName) {
			foreach ($this->roles as $role){

				//if($role->name == "Admin") return true;

				if($role->name == $roleName) return true;
			}
			return false;
		//});
	}
	/**
	 * Scope Filter
	 */
	public function scopeFilter($query, array $filters)
	{
		$query->when($filters['search'] ?? null, function ($query, $search) {
			$query->where(function ($query) use ($search) {
				$query->where('name', 'like', '%'.$search.'%')
						->orWhere('email', 'like', '%'.$search.'%');
			});
		})->when(!in_array("viewAny-user", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
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
	 * The roles that belong to the user.
	 */
	public function roles()
	{
		return $this->belongsToMany(Role::class);
	}

	public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

	/**
	 * The permissions that belong to the user.
	 */
	public function permissions()
	{
		return $this->morphMany(Permission::class, 'permissionable');
	}

	public function userFields(){
        return $this->hasMany(UserField::class);
    }

	public function tripBookings()
	{
		return $this->belongsToMany(TripBooking::class);
	}

	public function userTrips(){
        return $this->hasMany(TripTourGuide::class);
    }

	public function userDropBox(){
        return $this->hasMany(UserDropboxLink::class);
    }
}
