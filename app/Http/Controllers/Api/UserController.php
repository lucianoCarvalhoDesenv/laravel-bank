<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User as User;
use App\Http\Resources\User as UserResource;

class UserController extends Controller
{
    public function index(){
        // $curUsers = User::paginate(15);
        $curUsers = User::all();
        return UserResource::collection($curUsers);
      }
    
      public function show($id){
        $curUsers = User::findOrFail( $id );
        return new UserResource( $curUsers );
      }
    
      public function store(Request $request){
        $curUser = new User;
        if( null !== ($request->input('id')) ){
          $curUser->id = $request->input('id');
        }
        $curUser->owner_id = $request->input('owner_id');
        $curUser->amount = 0;

        if( $curUser->save() ){
          return new UserResource( $curUser );
        }
      }
    
       public function update(Request $request){
        $curUser = User::findOrFail( $request->id );
        $curUser->owner_id = $request->input('owner_id');
        $curUser->amount = 0;
    
        if( $curUser->save() ){
          return new UserResource( $curUser );
        }
      } 
    
      public function destroy($id){
        $curUser = User::findOrFail( $id );
        if( $curUser->delete() ){
          return new UserResource( $curUser );
            }
    
        }
}
