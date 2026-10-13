<section class="bg-green-600 rounded-2xl p-10 text-center mt-12 no-print">
    <h2 class="text-2xl font-bold text-white mb-2">Evidencija gnojidbe i prskanja bez papira</h2>
    <p class="text-green-100 mb-6">AgroLog vodi zapise po ARKOD parcelama i ispisuje ih u standardnom obliku. Besplatno.</p>
    <a href="{{ route('register') }}" class="inline-block bg-white hover:bg-gray-50 text-green-700 font-bold px-8 py-3 rounded-xl transition shadow-md">Kreirajte račun</a>
</section>
<h2 class="text-xl font-bold text-gray-900 mt-12 mb-4 no-print">Ostali alati</h2>
<div class="grid sm:grid-cols-3 gap-4 no-print">
    @foreach ($ostali as $slug => $o)
        <a href="{{ route('alat', $slug) }}" class="block border border-gray-200 rounded-xl p-5 hover:border-green-400 transition">
            <div class="text-2xl mb-2">{{ $o['ico'] }}</div>
            <div class="font-semibold text-gray-900">{{ $o['naziv'] }}</div>
        </a>
    @endforeach
</div>
