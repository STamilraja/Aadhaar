<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use Illuminate\Validation\ValidationException;
use Response;
use Log;
use Validator;
use GuzzleHttp\Client;

/**
 * Class AadhaarVerificationController
 * @package App\Http\Controllers
 */
class AadhaarVerificationController extends Controller
{
    
  /**
   * Method for Aadhaar Number Verification.
   * @param  \Illuminate\Http\Request  $request
   * @return Response Verifies Aadhaar and returns respective message
   */
  public function adhaarVerification(Request $request)
  {
    Log::info('In AadhaarVerificationController/adhaarVerification');
    try {

    $validator = Validator::make($request->all(), [
        'aadhaarid' => 'required',
        'name' => 'required',
        'pincode' => 'required'
        ]);

    if ($validator->fails()) {
      return response(array(
        'message' => 'parameters missing',
        'missing_parameters' =>  $validator->errors()
      ), 400);
    }
     
    $aadhaarid = $request->input('aadhaarid');
    $name = $request->input('name');
    $pincode = $request->input('pincode');

    $client = new Client([ 'headers' => [ 'Content-Type' => 'application/json' ]]);
    $res = $client->post('http://128.199.150.145:5181/auth/raw',
        ['body' => json_encode(
            ['aadhaar-id'=>$aadhaarid,
                "location" => ["type" => "pincode", "pincode" => $pincode ],
                "modality" => "demo",
                "certificate-type" => "preprod",
                "demographics" =>
                ["name" =>
                    ["matching-strategy" => "exact",  "name-value" => $name]
                ]
            ]
        )]
    );
    $reply=json_decode($res->getBody()->getContents());

    if($res->getStatusCode()==200 && $reply->success)
      return response()->json(['message'=>'Aadhaar Details Verified'],200);
    else
      return response()->json(['errmessage'=>'Aadhaar Details Not Verified'],400);

    } catch (ModelNotFoundException $e) {
        Log::error(' Model Not Found');
        return response(array(
            'error' => 'Model not found'
        ), 400);
    }
  }
}