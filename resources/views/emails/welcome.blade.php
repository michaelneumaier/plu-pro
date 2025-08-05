@component('mail::message')
# Welcome to {{ config('app.name') }}!

Hi {{ $user->name }},

Thank you for creating an account with PLUPro! We're excited to have you join our community of produce professionals.

## What's Next?

With your new account, you can:
- Search thousands of PLU codes instantly
- Create and manage custom produce lists
- Generate barcodes for your products
- Access our comprehensive produce database

@component('mail::button', ['url' => url('/')])
Start Exploring PLUPro
@endcomponent

If you have any questions or need assistance, feel free to reach out to our support team.

Thanks,<br>
The {{ config('app.name') }} Team
@endcomponent