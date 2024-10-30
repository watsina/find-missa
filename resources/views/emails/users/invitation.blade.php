<x-mail::message>
{{ __('You have been invited to join and collaborate in Mass Finder.) }}

{{ __('You may accept this invitation by clicking the button below:') }}

<x-mail::button :url="$acceptUrl">
    {{ __('Accept Invitation') }}
</x-mail::button>

{{ __('If you did not expect to receive an invitation to this project, you may discard this email.') }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
