{{-- Puls (puls.on-click.hr): statistika posjeta bez kolačića i bez spremanja IP adrese. Samo na produkciji.
     S ključem (PULS_PROXY_KEY u .env) ide preko agro-log.app/s/…, inače izravno na Puls (Brave to blokira). --}}
@production
@if(config('services.puls.key'))
<script defer src="/s/app.js" data-site="{{ config('services.puls.site') }}" data-api="/s/e"></script>
@else
<script defer src="{{ config('services.puls.url') }}/p.js" data-site="{{ config('services.puls.site') }}"></script>
@endif
@endproduction
