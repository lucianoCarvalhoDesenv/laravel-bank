<?php

namespace App\Http\Controllers;

use App\Models\Wallet as Wallet;
use App\Http\Resources\Wallet as WalletResource;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(){
        // $curWallets = Wallet::paginate(15);
        $curWallets = Wallet::all();
        return WalletResource::collection($curWallets);
      }
    
      public function show($id){
        $curWallets = Wallet::findOrFail( $id );
        return new WalletResource( $curWallets );
      }
    
      public function store(Request $request){
        $curWallet = new Wallet;
        if( null !== ($request->input('id')) ){
          $curWallet->id = $request->input('id');
        }
        $curWallet->owner_id = $request->input('owner_id');
        $curWallet->amount = 0;

        if( $curWallet->save() ){
          return new WalletResource( $curWallet );
        }
      }
    
       public function update(Request $request){
        $curWallet = Wallet::findOrFail( $request->id );
        $curWallet->owner_id = $request->input('owner_id');
        $curWallet->amount = 0;
    
        if( $curWallet->save() ){
          return new WalletResource( $curWallet );
        }
      } 
    
      public function destroy($id){
        $curWallet = Wallet::findOrFail( $id );
        if( $curWallet->delete() ){
          return new WalletResource( $curWallet );
            }
    
        }
}
