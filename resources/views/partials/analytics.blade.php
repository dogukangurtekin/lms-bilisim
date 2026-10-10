{{-- Çerezsiz, gizlilik dostu analytics (Plausible). .env içine PLAUSIBLE_DOMAIN=bilisimkod.com yazılınca etkinleşir. --}}
@if(config('services.plausible.domain'))
<script defer data-domain="{{ config('services.plausible.domain') }}" src="https://plausible.io/js/script.js"></script>
@endif
