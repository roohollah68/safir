<x-guest-layout>
    <x-auth-card>


        <div class="mb-4 text-sm text-gray-600">
            {{ __('رمز عبور خود را فراموش کردید؟ مشکلی نیست. با ادمین تماس بگیرید تا مشکل شما را حل کند.') }}
            {{ __('راههای ارتباطی با ادمین:') }}
        </div>
        <div class="mb-4 text-sm text-gray-600">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                {{ __('بازگشت') }}
            </a>
        </div>
    </x-auth-card>
</x-guest-layout>
