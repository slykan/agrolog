<x-guest-layout>
    @if(! $pristup || $pristup->status === 'opozvano')
        <h1 class="text-lg font-semibold text-gray-900 mb-2">Poziv više ne vrijedi</h1>
        <p class="text-sm text-gray-600">Gospodarstvo je povuklo poziv ili je poveznica neispravna. Zatražite od gospodarstva novi poziv.</p>
    @elseif($pristup->status === 'prihvaceno')
        <h1 class="text-lg font-semibold text-gray-900 mb-2">Poziv je već prihvaćen</h1>
        <p class="text-sm text-gray-600 mb-4">Gospodarstvo je na vašem popisu klijenata.</p>
        <a href="{{ route('klijenti') }}" class="inline-flex items-center px-4 py-2 bg-green-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800">Moji klijenti</a>
    @else
        @php $g = $pristup->vlasnik->naziv_gospodarstva ?: $pristup->vlasnik->name; @endphp
        <p class="text-xs font-semibold uppercase tracking-widest text-green-700 mb-1">Poziv savjetniku</p>
        <h1 class="text-xl font-bold text-gray-900 mb-3">{{ $g }} vas poziva kao savjetnika</h1>
        <div class="rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-gray-700 mb-5">
            Prava: <strong>{{ \App\Models\Pristup::PRAVA[$pristup->prava] }}</strong>.
            @if($pristup->prava === 'unos')
                Moći ćete voditi evidenciju gospodarstva, a svaka vaša izmjena bilježi se u povijest koju gospodarstvo vidi.
            @else
                Moći ćete pregledavati evidenciju i ispisivati PDF, bez izmjena.
            @endif
        </div>

        @if($ja)
            @if($ja->id === $pristup->vlasnik_id)
                <p class="text-sm text-red-700">Ovo je poziv koji ste vi poslali — otvorite poveznicu s računom savjetnika.</p>
            @else
                <p class="text-sm text-gray-600 mb-4">Prijavljeni ste kao <strong>{{ $ja->name }}</strong> ({{ $ja->email }}).</p>
                <form method="post" action="{{ route('poziv.prihvati', $pristup->token) }}">
                    @csrf
                    <x-primary-button class="w-full justify-center py-3">Prihvati poziv</x-primary-button>
                </form>
            @endif
        @else
            <p class="text-sm text-gray-600 mb-4">Za prihvaćanje se prijavite ili besplatno registrirajte — nakon toga se vraćate ovdje.</p>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-4 py-3 bg-green-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800">Prijava</a>
                <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-4 py-3 border border-green-900 rounded-md font-semibold text-xs text-green-900 uppercase tracking-widest hover:bg-green-50">Registracija</a>
            </div>
        @endif
    @endif
</x-guest-layout>
