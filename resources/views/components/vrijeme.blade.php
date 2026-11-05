{{--
    Vrijeme u 24-satnom obliku (hh:mm), bez AM/PM — ugrađeno polje type="time" prikazuje AM/PM prema jeziku sustava.
    Upis "7", "730", "7.30" ili "0730" na izlasku iz polja postane "07:00" / "07:30".
--}}
@props(['placeholder' => 'hh:mm'])
<input type="text" inputmode="numeric" maxlength="5" autocomplete="off" placeholder="{{ $placeholder }}"
       x-data="{
           cisti(el) { el.value = el.value.replace(/[^0-9:.,]/g, '').slice(0, 5) },
           sredi(el) {
               let s = el.value.trim().replace(/[.,]/, ':');
               if (s === '') return;
               let h, m;
               if (s.includes(':')) { [h, m] = s.split(':'); m = m || '0' }
               else if (s.length <= 2) { h = s; m = '0' }
               else { h = s.slice(0, s.length - 2); m = s.slice(-2) }
               h = parseInt(h, 10); m = parseInt(m, 10);
               if (isNaN(h) || isNaN(m) || h > 23 || m > 59) return;
               const novo = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
               if (novo !== el.value) { el.value = novo; el.dispatchEvent(new Event('input', { bubbles: true })) }
           },
       }"
       x-on:input="cisti($el)" x-on:blur="sredi($el)"
       {{ $attributes->merge(['class' => 'form-input vrijeme-24 tabular-nums']) }}>
