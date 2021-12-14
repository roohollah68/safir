<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    public function createFromTelegram($name, $phone, $telegram_id)
    {
        return view('auth.register', ['name' => $name, 'phone' => $phone, 'telegram_id' => $telegram_id]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|min:5|unique:users',
            'phone' => 'required|string|max:11|min:11|unique:users',
            'telegram_id' => 'numeric|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $request->phone = $this->number_Fa_En($request->phone);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'telegram_id' => $request->telegram_id,
            'password' => Hash::make($request->password),
            'telegram_code' => Str::random(40),
        ]);
        Auth::login($user);

        event(new Registered($user));

        return redirect()->route('listOrders');
    }

    public function newUserMessage()
    {
        return view('auth.new-user-message');
    }

    public function fromTelegram(Request $request)
    {
        auth()->logout();
        $name = $request['name'];
        $phone = $request['phone'];
        $telegram_id = $request['telegram_id'];
        return redirect("register/$name/$phone/$telegram_id");
    }
}
