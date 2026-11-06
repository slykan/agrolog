import './bootstrap';

import Alpine from 'alpinejs';
import { poljaTraktori } from './polja';
import { pokreniPush } from './push';

// pozadina od njiva (naslovnica i aplikacija); mora biti globalno prije pokretanja Alpinea
window.poljaTraktori = poljaTraktori;

pokreniPush();

window.Alpine = Alpine;

Alpine.start();
