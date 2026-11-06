import './bootstrap';

import Alpine from 'alpinejs';
import { poljaTraktori } from './polja';
import { pokreniPush } from './push';
import { pokreniOglase } from './oglasi';
import { ucrtavanjeParcele } from './ucrtavanje';

// pozadina od njiva (naslovnica i aplikacija); mora biti globalno prije pokretanja Alpinea
window.poljaTraktori = poljaTraktori;
window.ucrtavanjeParcele = ucrtavanjeParcele;

pokreniPush();
pokreniOglase();

window.Alpine = Alpine;

Alpine.start();
