import './bootstrap';

import Alpine from 'alpinejs';
import { poljaTraktori } from './polja';
import { pokreniPush } from './push';
import { pokreniOglase } from './oglasi';

// pozadina od njiva (naslovnica i aplikacija); mora biti globalno prije pokretanja Alpinea
window.poljaTraktori = poljaTraktori;

pokreniPush();
pokreniOglase();

window.Alpine = Alpine;

Alpine.start();
