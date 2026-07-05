import './bootstrap';
import './ui';
import { initPublicAnimations } from './public-animations';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

document.addEventListener('alpine:init', () => {
    // Global dark-mode store — persisted in localStorage
    Alpine.store('theme', {
        dark: localStorage.getItem('theme') !== 'light',
        toggle() {
            this.dark = !this.dark;
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', this.dark);
        },
    });
});

Alpine.start();

// Run public animations only on public-facing pages
document.addEventListener('DOMContentLoaded', () => {
    if (document.body.dataset.public) {
        initPublicAnimations();
    } else {
        document.body.classList.add('page-loaded');
    }
});
