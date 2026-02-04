import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Auto-apply dark or light mode on every page load based on user preference
try {
	const theme = localStorage.getItem('thera_theme');
	if (theme === 'dark') {
		document.documentElement.classList.add('dark');
		document.body.style.backgroundColor = '#0f172a';
	} else if (theme === 'light') {
		document.documentElement.classList.remove('dark');
		document.body.style.backgroundColor = '';
	}
} catch (e) {}
