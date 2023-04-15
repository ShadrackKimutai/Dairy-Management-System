<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = "roles";
    protected $fillable = ['name','description','level'];
    protected $primaryKey = "role_id";


    public function users(){
        return $this->hasMany("App\User");
    }
}
