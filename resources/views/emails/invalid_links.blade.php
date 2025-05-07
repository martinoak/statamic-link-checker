<x-mail::message>
    @foreach($errors as $status => $error)
        # {{ $status }}<br>
        @if(is_array($error))
            @foreach($error as $line)
                <nobr>{{ $line }}</nobr><br>
            @endforeach
        @else
            <nobr>{{ $error }}</nobr><br>
        @endif
    @endforeach
</x-mail::message>
