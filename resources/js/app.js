import './bootstrap';

import Alpine from 'alpinejs';
import { poljaTraktori } from './polja';

// pozadina od njiva (naslovnica i aplikacija); mora biti globalno prije pokretanja Alpinea
window.poljaTraktori = poljaTraktori;

window.Alpine = Alpine;

Alpine.start();
