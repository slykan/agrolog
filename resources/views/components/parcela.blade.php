@props(['broj' => null, 'naziv' => null])
{{-- ARKOD broj s vlastitim nazivom parcele, naziv diskretno sivo: 1708275 · Iza kuće --}}
{{ $broj }}@if(filled($naziv))<span class="font-normal text-gray-500"> · {{ $naziv }}</span>@endif
