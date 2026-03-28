<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Gnojidba;

class GnojidbaTable extends Component
{
    public $showForm = false;
    public $form = [];
    public $editForm = [];
    public $editingId = null;
    public $visibleColumns = ['arkod','povrsina','kultura','datum','gnojivo','kolicina'];

    // Property za select kulturu
    public function getKultureProperty()
    {
        return \App\Models\Kultura::join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
            ->where('kulture.user_id', auth()->id())
            ->orderBy('parcele.arkod_broj')
            ->orderBy('kulture.naziv')
            ->select('kulture.*', 'parcele.arkod_broj', 'parcele.povrsina_ha')
            ->get();
    }

    // Property za prikaz svih gnojidbi sa join-om
    public function getGnojidbeProperty()
    {
        return Gnojidba::where('gnojidbe.user_id', auth()->id())
            ->join('kulture', 'kulture.id', '=', 'gnojidbe.kultura_id')
            ->join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
            ->select('gnojidbe.*', 'kulture.naziv as kultura_naziv', 'kulture.posadjena_povrsina_ha', 'parcele.arkod_broj', 'parcele.povrsina_ha')
            ->orderByDesc('gnojidbe.datum')
            ->orderByDesc('gnojidbe.id')
            ->get();
    }

    // Property za predloge tipova gnojiva
    public function getTipoviGnojivaProperty()
    {
        return Gnojidba::where('gnojidbe.user_id', auth()->id())
            ->distinct()
            ->orderBy('tip_gnojiva')
            ->pluck('tip_gnojiva')
            ->filter();
    }
}
