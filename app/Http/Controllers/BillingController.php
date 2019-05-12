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
     * Generate iccid, imsi, pin1 and imsi numbers
     */
    public function randomNumber($length) {
        $result = '';
        for($i = 0; $i < $length; $i++) {
            $result .= mt_rand(1, 9);
        }
        return $result;
    }

    public function createSimCard(Request $request){

        //validate input
        $validator = Validator::make($request->all(), [
            'ki' => 'required|string|min:20|max:20'
        ]);

        if ($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 401);
        }



        $iccid = '8925402'.$this->randomNumber(14);
        $imsi = '63902'.$this->randomNumber(10);
        $pin1 = $this->randomNumber(4);
        $puc = $this->randomNumber(6);

        try{
            $sim = Sim::where('iccid', '=', $iccid)->firstOrFail();

        }
        catch ( ModelNotFoundException $e) {
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
                return response()->json(['status'=>'10', 'data'=>'SIM error creating sim', 'error'=>$e->errorInfo, 'iccid'=>$iccid]);
            }

            return response()->json(['status'=>'0', 'data'=>'Success']);

        }
        return response()->json(['status'=>'1', 'data'=>'Sim already provisioned']);

    }

    /**
     * Ths endpoint activates the simcard /POST/
     */
    public function activateSimCard(Request $request){
        //Get sim with associated ICCID
        $validator = Validator::make($request->all(), [
            'iccid' => 'required|string|min:21|max:21',
            'msisdn' => 'required|string|min:12|max:12'
        ]);

        if ($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 401);
        }


        try{
            $sim = Sim::where('iccid', '=', $request['iccid'])->firstOrFail();

        }
        catch ( ModelNotFoundException $e) {
            return response()->json(['status'=>'1', 'data'=>'SIM card does not exist']);
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

            }

            catch (QueryException $e){
                return response()->json(['error'=>'error ativating sim', 'data'=>$e->errorInfo, 'msg'=>$sim->iccid]);

            }

            DB::table('sims')
            ->where('id', $sim->id)
            ->update(['status' => 1]);
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

        //get subscriber info

        try{
            $subscriber = msisdn::where('msisdn', '=', $request['msisdn'])
            ->join('sims', 'sims.id', '=', 'msisdns.iccid')
            ->select('sims.*', 'msisdns.balance', 'msisdns.msisdn')->firstOrFail();
        }
        catch(ModelNotFoundException $e){
            return response()->json(['status'=>'0', 'data'=>'Subscriber not found']);

        }

        return response()->json(['status'=>'1', 'data'=>$subscriber]);




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
