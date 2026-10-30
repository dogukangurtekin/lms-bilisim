@if($token)
    <a href="{{ route('teacher.assignments.public-entries', $token) }}" title="Giriş yapanları gör">{{ (int) ($entryCounts[$token] ?? 0) }} giriş</a>
@else
    -
@endif
