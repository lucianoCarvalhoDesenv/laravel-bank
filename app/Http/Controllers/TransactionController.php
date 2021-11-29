<?php

namespace App\Http\Controllers;

use App\Models\Transaction as Transaction;
use App\Http\Resources\Transaction as TransactionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use Nette\Utils\DateTime;
use Exception;
use App\Http\Requests\ApproveRequest ;
use App\Http\Requests\TransactionRequest ;
use App\Http\Requests\CustumerRequest;
use App\Http\Requests\AdminRequest;
use App\Http\Requests\JWTRequest as LoggedRequest;
use App\Models\User as User;
use App\Http\Resources\User as UserResource;


class TransactionController extends Controller
{
    /**
     * Display a listing of user transaction resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(CustumerRequest $request)
    {      

       
        $userid = auth()->user()->id;
        $curTransaction = Transaction::owner($userid)->approved('Y')->orderBy('order', 'DESC')->get();
        
       //->approved('Y')
        return TransactionResource::collection($curTransaction);
    }

     /**
     * Display a listing of user payments
     *
     * @return \Illuminate\Http\Response
     */
    public function expenses(CustumerRequest $request)
    {
       
        $userid = auth()->user()->id;
        $curTransaction = Transaction::owner($userid)->approved('Y')->type('P')->orderBy('order', 'DESC')->get();
        return TransactionResource::collection($curTransaction);
    }

       /**
     * Display a listing of user accepetd incomes
     *
     * @return \Illuminate\Http\Response
     */
    public function incomes(CustumerRequest $request)
    {
       
        $userid = auth()->user()->id;
        $curTransaction = Transaction::owner($userid)->approved('Y')->type('D')->orderBy('order', 'DESC')->get();
        return TransactionResource::collection($curTransaction);
    }

        /**
     * Display a listing of user accepetd incomes
     *
     * @return \Illuminate\Http\Response
     */
    public function mychecks(CustumerRequest $request)
    {
       
        $userid = auth()->user()->id;
        $approved = Transaction::owner($userid)->approved('Y')->type('D')->orderBy('order', 'DESC')->get();
        $pending = Transaction::owner($userid)->approved('W')->type('D')->orderBy('order', 'DESC')->get();
        $rejected = Transaction::owner($userid)->approved('N')->type('D')->orderBy('order', 'DESC')->get();
       
        return  response()->json(['approved' => TransactionResource::collection($approved),
                                'pending' => TransactionResource::collection($pending),
                                'rejected'=>TransactionResource::collection($rejected) ]); 
    }


        /**
     * Display a listing of user transaction resource.
     *
     * @return \Illuminate\Http\Response
     */
    //TODO: Create AdminRequest
    public function waitingtransactions(AdminRequest $request)
    {
       
        $curTransaction = Transaction::where('approved','W')->paginate(50);
       
        return TransactionResource::collection($curTransaction);
    }

    

    public function getbyid(LoggedRequest $request)
    {
       
        $user =auth()->user();
        $userid = auth()->user()->id;
        if($user->type === 'admin'){
            $transaction= Transaction::where('id',$request->id)->first(); 
            $userID= $transaction->owner;
            $user =   User::findOrFail( $userID ); 
            return  response()->json(['transaction' => $transaction,  'user' => $user  ]); 
        }
        else{
            $transaction= Transaction::where('owner', $userid)->where('id',$request->id)->first(); 
        }


        
        return new TransactionResource( $transaction );;
    }


    public function balance(CustumerRequest $request)
    {
       
        $userid = auth()->user()->id;

        $balance= Transaction::where('owner', $userid)->where('approved','Y')->latest('order')->first(); 
        
        if(!isset($balance) ){
            $pre_trans= new Transaction;
            $pre_trans->balance_after=0;
        }
        
        return response()->json(['balance' => $balance->balance_after]);
    }

      /**
     * link new transaction to last customer approved transaction
     * 
     * this operation calculate the new balance
     * for this consider the absolute value of amount in new transaction
     * the signal depends ON type (P)ayment [-] ; (D)eposit [+]
     *
     * @param  \App\Models\Transaction   $new_transaction
     * @param  \App\Models\Transaction   $last_customer_approved_transaction
     * @return \Illuminate\Http\Response
     */
    private function link_transaction(Transaction $transaction,Transaction $pre_trans ){
   
        $transaction->pre_transaction = $pre_trans->id; 

        if($transaction->type == 'D') {
            $transaction->balance_after=round(($pre_trans->balance_after + abs($transaction->amount)), 2);}
        else if($transaction->type == 'P'){
            
            $transaction->balance_after=round(($pre_trans->balance_after - abs($transaction->amount)), 2);}
        else{
            return response()->json(['error_message' => 'Invalid transaction Type']); 
        }    

        if( $transaction->balance_after<0){
            return response()->json(['error' => 'Unauthorized', 'message' =>'Balance not available for this payment!'], 401);}
        
        $transaction->approved='Y'; //approved YES
            
        $transaction->order =$pre_trans->order+1;  //if approved inc ORDER COUNT    
        
        if( $transaction->save() ){
          return new TransactionResource( $transaction );
        }
    }

    public function approveCheck(ApproveRequest $request){
        
        $request->validated(); 
      
        $transaction= Transaction::where('approved','W')->where('id',$request->transaction )->first();        
        
        if(!isset($transaction) ){
            return response()->json(['error_message' => 'InvalidTransaction']);
        }
        
        if($request->approved== 'N')
        {
            $transaction->approved=$request->approved;
            try{
                $transaction->save();
                return new TransactionResource( $transaction );
                }
            catch(Exception $e)   {
                return response()->json(['error_message' => $e->getMessage()]);
                }             
        }
        
        $pre_trans= Transaction::where('owner', $transaction->owner)->where('approved','Y')->latest('order')->first();        
        
        //if first transaction
        if(!isset($pre_trans) ){
            $pre_trans= new Transaction;
            $pre_trans->amount = 0;
            $pre_trans->balance_after=0;
            $pre_trans->order=0;
        }

        if( null !== ($request->input('date')) ){
            $transaction->date = $request->input('date');
          }
        else { 
        $transaction->date = (new DateTime('now'))->format('Y-m-d H:i:s');}

        return $this->link_transaction($transaction,$pre_trans );
    }
 
   

    
    public function submit_check(TransactionRequest $request)
    {       
        $request->validated(); 
    
        $transaction = new Transaction;
        $transaction->owner = auth()->user()->id;
        $transaction->amount = $request->input('amount');
        $transaction->description = $request->input('description');
        $transaction->type ='D';//type Payment    
        $transaction->approved='W'; //approved WAITING
        $transaction->order =-1;  //  not ordered  
    
        if( null !== ($request->input('date')) ){
            $transaction->date = $request->input('date');
          }
        else { 
        $transaction->date = (new DateTime('now'))->format('Y-m-d H:i:s');}

        if ($file = $request->input('imgurl')) {
            list($type, $imageData) = explode(';', $request->input('imgurl'));
            list(, $extension) = explode('/', $type);
            list(, $imageData) = explode(',', $imageData);
            $fileName = uniqid() . '.' . $extension;
            $source = fopen($request->input('imgurl'), 'r');
            $destination = fopen($_SERVER['DOCUMENT_ROOT'].'/images/' . $fileName, 'w');
            stream_copy_to_stream($source, $destination);
            fclose($source);
            fclose($destination);
            $transaction->imgurl = "/images/" . $fileName;
         }
        
        if( $transaction->save() ){
          return new TransactionResource( $transaction );
        }

    }
  
    
    public function payment(TransactionRequest $request)
    {       
        $request->validated();    
        $userid = auth()->user()->id;
        $pre_trans= Transaction::where('owner', $userid)->where('approved','Y')->latest('order')->first();        

        if(!isset($pre_trans) ){
            $pre_trans= new Transaction;
            $pre_trans->owner = $userid;
            $pre_trans->amount = 0;
            $pre_trans->balance_after=0;
            $pre_trans->order=0;
        }
        //return response()->json(['error_message' => 'no account balance']); 

        //dd($pre_trans->owner, $pre_trans->balance_after);   
        $transaction = new Transaction;
        $transaction->owner = $userid;
        $transaction->amount = $request->input('amount');
        $transaction->description = $request->input('description');
      

        $transaction->type ='P';//type Payment        
        if( null !== ($request->input('date')) ){
            $transaction->date = $request->input('date');
          }
        else { 
        $transaction->date = (new DateTime('now'))->format('Y-m-d H:i:s');}
        
        return $this->link_transaction($transaction,$pre_trans);
    }


    /**
     * Only TEST api used to simulate to payment at sametime
     * 
     * Request  Sample:{
     * "pre_transaction":36,
     * 	"amount":99.52,	
     * "description":"compras" }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function test_force_transaction(CustumerRequest $request)
    {
        $user = auth()->user();
        $userid = $user->id;

        //|DEBUG|simulate enter same time with other transaction 
        $pre_trans= Transaction::where('id', $request->pre_transaction)->first();        
       
        if(!isset($pre_trans) ){
            return response()->json([ 'error_message' =>'Test fail (hint: pass valid >pre_transaction< )'], 401);
        }
         
        $transaction = new Transaction;
        $transaction->owner = $user->id;
        $transaction->amount = $request->input('amount');
        $transaction->description = $request->input('description');
      

        $transaction->type ='P';//type Payment        
        if( null !== ($request->input('date')) ){
            $transaction->date = $request->input('date');
          }
        else { 
        $transaction->date = (new DateTime('now'))->format('Y-m-d H:i:s');}
        $transaction->pre_transaction = $pre_trans->id; 
        
        $transaction->balance_after=$pre_trans->balance_after - abs($transaction->amount);
        
        if( $transaction->balance_after<0){
            return response()->json(['error' => 'Unauthorized', 'message' =>'Balance not available for this payment!'], 401);}
        
        $transaction->approved='Y'; //approved YES
            
        $transaction->order =$pre_trans->order+1;      
        
        if( $transaction->save() ){
          return new TransactionResource( $transaction );
        }
    }

    /**
     * Only TEST off cash auto_approved transaction
     * >>route commetted
     * @param  \Illuminate\Http\TransactionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function auto_approved_deposit(TransactionRequest $request)
    {
        
        $request->validated(); 
        $user =auth()->user();
        $userid = $user->id;
        
        $pre_trans= Transaction::where('owner', $userid)->where('approved','Y')->latest('order')->first();
        

        if(!isset($pre_trans) ){
            $pre_trans= new Transaction;
            $pre_trans->owner = $user->id;
            $pre_trans->amount = 0;
            $pre_trans->balance_after=0;
            $pre_trans->order=0;
        }
        
        
        $transaction = new Transaction;
        $transaction->owner = $user->id;
        $transaction->amount = $request->input('amount');
        $transaction->description = $request->input('description');
      

        $transaction->type ='D';
        $transaction->approved='Y';        
        if( null !== ($request->input('date')) ){
            $transaction->date = $request->input('date');
          }
        else { 
        $transaction->date = (new DateTime('now'))->format('Y-m-d H:i:s');}

        return $this->link_transaction($transaction,$pre_trans);
    }


}
