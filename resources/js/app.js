import './bootstrap';

import Alpine from 'alpinejs';
import { poljaTraktori } from './polja';
import { pokreniPush } from './push';
import { pokreniOglase } from './oglasi';
import { ucrtavanjeParcele } from './ucrtavanje';
import { lokacijaGospodarstva } from './lokacija-gospodarstva';

// pozadina od njiva (naslovnica i aplikacija); mora biti globalno prije pokretanja Alpinea
window.poljaTraktori = poljaTraktori;
window.ucrtavanjeParcele = ucrtavanjeParcele;
window.lokacijaGospodarstva = lokacijaGospodarstva;

pokreniPush();
pokreniOglase();

window.Alpine = Alpine;

Alpine.start();
