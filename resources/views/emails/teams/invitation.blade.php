<x-mail::message>
{{ __("You have been invited to join and collaborate with $teamName in the Find Missa project.") }}

{{ __('You may accept this invitation by clicking the button below:') }}

<x-mail::button :url="$acceptUrl">
    {{ __('Accept Invitation') }}
</x-mail::button>

{{ __('If you received this invitation without your consent, you may discard this email.') }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
