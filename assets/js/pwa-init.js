/*
  ═══════════════════════════════════════════════════════════════════════════
  PWA INITIALIZATION - Progressive Web App Setup
  सहकारी HRM & CMS System - PWA Support
  ═══════════════════════════════════════════════════════════════════════════
*/

(function() {
  'use strict';

  // Register Service Worker
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('/sw.js', { scope: '/' })
        .then(function(registration) {
          console.log('[PWA] Service Worker registered successfully:', registration);
          
          // Check for updates every hour
          setInterval(function() {
            registration.update();
          }, 60 * 60 * 1000);
        })
        .catch(function(error) {
          console.log('[PWA] Service Worker registration failed:', error);
        });
    });
  }

  // Handle "Add to Home Screen" prompt
  let deferredPrompt;
  window.addEventListener('beforeinstallprompt', function(e) {
    e.preventDefault();
    deferredPrompt = e;
    
    // Show your custom install button
    const installButton = document.getElementById('pwa-install-btn');
    if (installButton) {
      installButton.style.display = 'block';
      installButton.addEventListener('click', function() {
        if (deferredPrompt) {
          deferredPrompt.prompt();
          deferredPrompt.userChoice.then(function(choiceResult) {
            if (choiceResult.outcome === 'accepted') {
              console.log('[PWA] User accepted install prompt');
            }
            deferredPrompt = null;
            installButton.style.display = 'none';
          });
        }
      });
    }
  });

  // Handle app installed
  window.addEventListener('appinstalled', function() {
    console.log('[PWA] App was installed successfully');
    const installButton = document.getElementById('pwa-install-btn');
    if (installButton) {
      installButton.style.display = 'none';
    }
    // Track analytics if needed
    if (window.gtag) {
      gtag('event', 'app_installed');
    }
  });

  // Detect when app is running in standalone mode
  if (window.navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches) {
    document.body.classList.add('pwa-standalone');
    console.log('[PWA] Running in standalone mode');
  }

  // Handle online/offline
  window.addEventListener('online', function() {
    console.log('[PWA] Back online');
    document.body.classList.remove('offline');
    if (window.location.pathname.includes('offline')) {
      window.location.href = '/';
    }
  });

  window.addEventListener('offline', function() {
    console.log('[PWA] Going offline');
    document.body.classList.add('offline');
  });

  // Initial offline check
  if (!navigator.onLine) {
    document.body.classList.add('offline');
  }

  console.log('[PWA] Initialization complete');
})();
