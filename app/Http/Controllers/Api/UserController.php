<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Webkul\Customer\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Webkul\Customer\Mail\VerificationEmail;

class UserController extends Controller
{
    protected $customer;

    public function __construct(CustomerRepository $customer)
    {
        $this->customer = $customer;
    }

    public function createUser(Request $request)
    {
        $data = request()->input();
        $data['password'] = 'x123456';
        $data['first_name'] = '';
        $data['last_name'] = '';
        $data['password'] = bcrypt($data['password']);
        $data['channel_id'] = core()->getCurrentChannel()->id;

        $data['is_verified'] = 1;

        $data['customer_group_id'] = 1;

        $verificationData['email'] = $data['email'];
        $verificationData['token'] = md5(uniqid(rand(), true));
        $data['token'] = $verificationData['token'];

        Event::fire('customer.registration.before');

        $customer = $this->customer->create($data);

        Event::fire('customer.registration.after', $customer);
        $response = [
            'code' => 200,
            'message' => ''
        ];
        if ($customer) {
            try {
//                session()->flash('success', trans('shop::app.customer.signup-form.success'));

                Mail::send(new VerificationEmail($verificationData));
            } catch (\Exception $e) {
//                session()->flash('info', trans('shop::app.customer.signup-form.success-verify-email-not-sent'));

                $response['code'] = 200;
                $response['message'] = trans('shop::app.customer.signup-form.success-verify-email-not-sent');
            }

        } else {

//            session()->flash('error', trans('shop::app.customer.signup-form.failed'));

            $response['code'] = 500;
            $response['message'] = trans('shop::app.customer.signup-form.failed');
        }
        return $response;
    }
}
