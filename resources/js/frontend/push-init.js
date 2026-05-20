(async function initPush() {
  if (!('serviceWorker' in navigator) || !('Notification' in window)) return;

  try {
    const reg = await navigator.serviceWorker.register('/sw.js');

    if (Notification.permission === 'default') {
      await Notification.requestPermission();
    }

    if (Notification.permission !== 'granted') return;

    const token = localStorage.getItem('fcm_web_token');
    if (!token || !window.siteConfig?.isLoggedIn) return;

    await fetch('/api/push/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.siteConfig.csrfToken,
      },
      body: JSON.stringify({ token, device_type: 'web' }),
    });
  } catch (e) {
    console.warn('Push init failed', e);
  }
})();
