<x-mail::message>
    # Potentially harmful weather

    ## {{ $hour->format('d.m.Y H:i') }}

    | Parameter | Value |
    | - | - |
    @foreach($values as $name => $value)
        | {{ $name }} | {{ $value }} |
    @endforeach

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
