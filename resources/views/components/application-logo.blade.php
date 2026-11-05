{{-- Logotip agro-log.app. bijeli=true: inačica za tamnu podlogu (bijela slova i simbol). Veličinu odredite visinom (npr. class="h-9 w-auto"). --}}
@props(['bijeli' => false])
<img src="{{ asset($bijeli ? 'images/logo/agro-log-bijeli.png' : 'images/logo/agro-log.png') }}" alt="agro-log.app" width="821" height="180" {{ $attributes->merge(['class' => 'w-auto']) }}>
