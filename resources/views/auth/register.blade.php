<x-guest-layout>
    <x-auth-card>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors"/>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            @if(isset($telegram_id))
                <input type="hidden" name="telegram_id" value="{{$telegram_id}}">
            @endif

        <!-- Name -->
            <div class="required">
                <x-label for="name" :value="__('نام و نام خانوادگی')"/>
                @if(isset($name))
                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{$name}}" required
                             autofocus/>
                @else
                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required
                             autofocus/>
                @endif
            </div>

            <!-- Email Address -->
            <div class="mt-4 required">
                <x-label for="username" :value="__('نام کاربری')"/>

                <x-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')"
                         required/>
            </div><!-- Email Address -->

            <div class="mt-4 required">
                <x-label for="phone" :value="__('شماره تماس')"/>
                @if(isset($phone))
                    <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" value="{{$phone}}"
                             readonly />
                @else
                    <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')"
                             minlength="11" maxlength="11" pattern="^[۰-۹0-9]*$" placeholder="خط دارای تلگرام"/>
                @endif
            </div>

            <!-- Password -->
            <div class="mt-4 required">
                <x-label for="password" :value="__('رمز عبور')"/>

                <x-input id="password" class="block mt-1 w-full"
                         type="password"
                         name="password"
                         required autocomplete="new-password"/>
            </div>

            <!-- Confirm Password -->
            <div class="mt-4 required">
                <x-label for="password_confirmation" :value="__('رمز عبور مجدد')"/>

                <x-input id="password_confirmation" class="block mt-1 w-full"
                         type="password"
                         name="password_confirmation" required/>
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                    {{ __('حساب کاربری دارید؟') }}
                </a>

                <x-button class="ml-4">
                    {{ __('ثبت نام') }}
                </x-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
