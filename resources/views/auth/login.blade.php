<x-guest-layout>
    <x-auth-card>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')"/>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors"/>

        <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
            <div>
                <x-label for="username" :value="__('نام کاربری')"/>

                <x-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')"
                         required autofocus/>
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-label for="password" :value="__('رمز عبور')"/>

                <x-input id="password" class="block mt-1 w-full"
                         type="password"
                         name="password"
                         required autocomplete="current-password"/>
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                           name="remember">
                    <span class="ml-2 text-sm text-gray-600">{{ __('مرا به خاطر بسپار') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900"
                       href="{{ route('password.request') }}">
                        {{ __('رمز عبور خود را فراموش کرده اید؟') }}
                    </a>
                    &nbsp;&nbsp;&nbsp;
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('register') }}">
                        {{ __('حساب کاربری ندارید؟') }}
                    </a>
                    &nbsp;&nbsp;&nbsp;
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="https://t.me/safir_order_bot">
                        {{ __('ورود با تلگرام') }}
                    </a>
                    &nbsp;&nbsp;&nbsp;
                @endif
                <x-button class="ml-3">
                    {{ __('ورود') }}
                </x-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
