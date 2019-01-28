<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Charge;
use App\User;
use App\Payment;
class PaymentController extends Controller
{
    public function CreatePayment(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'amount' => 'required|integer'
        ]);
        try {
            $user = User::where('userId', $request->userId)->firstOrFail();
            Stripe::setApiKey(env('STRIPE_API_KEY'));
            $charge = Charge::create(['amount' => $request->amount, 'currency' => 'usd', 'source' => $request->token]);
            $payment = new Payment;
            $payment->user()->associate($user->userId);
            $payment->amount = $charge->amount;
            $payment->payment_id = $charge->id;
            $payment->paid = $charge->paid;
            $payment->card_id = $charge->source->id;
            $payment->name = $charge->source->name;
            $payment->save();
            $user->credits = $user->credits + 100;
            $user->save();
            return response()->json(['credits' => $user->credits, 'amount' => $payment->amount]);
          
        }
        catch(\Stripe\Error\Card | \Stripe\Error\RateLimit | \Stripe\Error\InvalidRequest | \Stripe\Error\Authentication | \Stripe\Error\ApiConnection $e) 
        {
            $body = $e->getJsonBody();
            $err  = $body["error"];
            $return_array = [
                "status" =>  $e->getHttpStatus(),
                "type" =>  $err["type"],
                "code" =>  $err["code"],
                "message" =>  $err["message"],
            ];
            $return_str = json_encode($return_array);          
            return response()->json($return_array);
        }
        
    }
}