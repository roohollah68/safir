<x-guest-layout>
    <x-auth-card>


        <div class="mb-4 text-sm text-gray-600">
            {{ __('تبریک ! حساب کاربری شما ساخته شد، ولی هنوز فعال نیست.') }}
        </div>
        <div class="mb-4 text-sm text-gray-600">
            {{ __('منتظر فعال سازی از سوی همکاران ما باشید.') }}
        </div>
        <div class="mb-4 text-sm text-gray-600">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                {{ __('بازگشت') }}
            </a>
        </div>
    </x-auth-card>
</x-guest-layout>
