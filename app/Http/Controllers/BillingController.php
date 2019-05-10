<?php

namespace App\Http\Controllers;

use App\msisdn;
use App\Sim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BillingController extends Controller
{
    //
    /**
     * Restrict access to controller's resources for unauthenticated users
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * This endpoint creates a simcard /POST/
     */

    /**
     * Generate iccid numbers
     */
    public function randomNumber($length) {
        $result = '';
        for($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }
        return $result;
    }

    public function createSimCard(Request $request){

        //validate input
        $validator = Validator::make($request->all(), [
            'iccid' => 'required|',
            'ki' => 'required|string|min:20',
            'imsi' => 'required|integer|min:15',
            'pin1' => 'required|integer|min:4',
            'puc' => 'required|integer|min:6'
        ]);

        if ($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 401);
        } 


        
        $iccid = $request['iccid'];
        $imsi = $request['imsi'];
        $pin1 = $request['pin1'];
        $puc = $request['puc'];
        


        if ($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 401);
        } 

        try{
            Sim::create([
                'iccid'=>$iccid,
                'imsi'=>$imsi,
                'pin1'=>$pin1,
                'puc'=>$puc,
                'ki'=>$request['ki']
            ]);
        }
        catch ( QueryException $e) {
            return response()->json(['status'=>'1', 'data'=>'SIM already provisioned', 'error'=>$e->errorInfo, 'iccid'=>$iccid]);
        }

        return response()->json(['status'=>'0', 'data'=>'Success']);


        
    }

    /**
     * Ths endpoint activates the simcard /GET/
     */
    public function activateSimCard(Request $request){
        //Get sim with associated ICCID
        $validator = Validator::make($request->all(), [
            'iccid' => 'required|int',
            'msisdn' => 'required|int|min:12'            
        ]);

        if ($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 401);
        } 


        try{
            $sim = DB::table('sims')->where('iccid', $request['iccid'])->first();
   
        } 
        catch ( QueryException $e) {
            return response()->json(['status'=>'1', 'data'=>'SIM card does not exist', 'error'=>$e->errorInfo, 'iccid'=>$iccid]);
        }

        if($sim->status==1){
            return response()->json(['status'=>'2', 'data'=>'SIM already active']);
            
        }

        else{
            try{
                msisdn::create([
                    'msisdn'=>$request['msisdn'],
                    'iccid'=>$sim->id
                ]);

                $sim->status = 1;
            }

            catch (QueryException $e){
                return response()->json(['error'=>'error ativating sim', 'data'=>$e->errorInfo, 'msg'=>$sim->iccid]);

            }
        }



        return response()->json(['status'=>'0', 'data'=>'Success']);

    }

    /**
     * This endpoint queries subscriber information 
     */
    public function subscriberInfo(Request $request){
        //
        //validate msisdn input
        $validator = Validator::make($request->all(), [
            'msisdn' => 'required|numeric|min:12'            
        ]);

        if ($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 401);
        } 

        //find subscriber
        try{
            
            $msisdn = DB::table('msisdns')->where('msisdn', $request['msisdn'])->first(); 
   
        } 
        catch ( QueryException $e) {
            return response()->json(['status'=>'0', 'data'=>'Subscriber not found', 'error'=>$e->errorInfo]);
        }


        //find sim card information
        $iccid = $msisdn->iccid;
        try{
            $info = DB::table('sims')->where('id', $iccid)->first();
   
        } 
        catch ( QueryException $e) {
            return response()->json(['status'=>'1', 'data'=>'SIM card does not exist', 'error'=>$e->errorInfo, 'iccid'=>$iccid]);
        }


        $data = [
            'msisdn'=> $msisdn,
            'iccid'=> $info->iccid,
            'balance'=> $msisdn->balance,
            'iccid' => $info->iccid,
            'imsi' => $info->imsi,
            'ki' => $info->ki
        ];

        return response()->json(['status'=>'1', 'data'=>$data]);




    }

    /**
     * This endpoint adjusts the account balance 
     */
    public function adjustBalance(Request $request){
        //validate input
        // transaction type 1:Topup 2:topdown
        $validator = Validator::make($request->all(), [
            'msisdn' => 'required|int|min:12',
            'transactiontype' => 'required|int',
            'amount' => 'required|int'
                        
        ]);

        if ($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 401);
        }

        
        //find subscriber
        try{
             
            $msisdn = DB::table('msisdns')->where('msisdn', $request['msisdn'])->first(); 
   
        } 
        catch ( QueryException $e) {
            return response()->json(['status'=>'0', 'data'=>'Subscriber not found', 'error'=>$e->errorInfo]);
        }

        //check transaction type
        if($request['transactiontype'] == 1){

            
            $new_balance = $msisdn->balance + $request['amount'];
            
            DB::table('msisdns')
            ->where('id', $msisdn->id)
            ->update(['balance' => $new_balance]);

            
        }
        elseif($request['transactiontype'] == 0){
            if($msisdn->balance >= $request['amount']){
                $new_balance = $msisdn->balance - $request['amount'];
                DB::table('msisdns')
            ->where('id', $msisdn->id)
            ->update(['balance' => $new_balance]);
            }
            else{
                return response()->json(['status'=>'0', 'data'=>'Transaction not possible']);
            }
        }
        else{
            return response()->json(['status'=>'0', 'data'=>'Your transaction type is incorrect. Option is 1 for top up and 0 for debit']);
        }
        return response()->json(['status'=>'1', 'data'=>'Transaction successful']);
    }
}
