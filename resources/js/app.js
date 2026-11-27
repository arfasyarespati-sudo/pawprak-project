import Alpine from 'alpinejs';
import { CoretChaosApp } from './coret-chaos/CoretChaosApp';

window.Alpine = Alpine;
Alpine.start();

/**
 * CORET! CHAOS — Modular Real-Time Multiplayer Client Entrypoint.
 */
document.addEventListener('DOMContentLoaded', () => {
    if (window.CORET_CHAOS_BOOT) {
        window.coretChaosApp = new CoretChaosApp();
    }
});
