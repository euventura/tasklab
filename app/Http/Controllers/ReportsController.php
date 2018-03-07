<?php
/**
 * Created by PhpStorm.
 * User: euventura
 * Date: 3/6/18
 * Time: 1:22 PM
 */

namespace App\Http\Controllers;


use Auth0\Login\facade\Auth0;

class ReportsController extends Controller
{

    public function login()
    {
        return Auth0::login(null, null, ['scope' => 'openid profile email']);
    }

}