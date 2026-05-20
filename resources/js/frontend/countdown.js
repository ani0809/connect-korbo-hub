class CountdownTimer {
  constructor(endTime, elementId) {
    this.endTime = new Date(endTime);
    this.element = document.getElementById(elementId);
    if (this.element) this.start();
  }
  start() { this.update(); this.interval = setInterval(() => this.update(), 1000); }
  update() {
    const diff = this.endTime - new Date();
    if (diff <= 0) { clearInterval(this.interval); this.element.innerHTML = '<span class="expired">Expired</span>'; setTimeout(() => window.location.reload(), 2000); return; }
    const d = Math.floor(diff / 86400000), h = Math.floor((diff % 86400000) / 3600000), m = Math.floor((diff % 3600000) / 60000), s = Math.floor((diff % 60000) / 1000);
    this.element.innerHTML = `${d > 0 ? `<span class="cd-unit"><span class="cd-num">${String(d).padStart(2,'0')}</span><span class="cd-label">d</span></span>` : ''}<span class="cd-unit"><span class="cd-num">${String(h).padStart(2,'0')}</span><span class="cd-label">h</span></span><span class="cd-sep">:</span><span class="cd-unit"><span class="cd-num">${String(m).padStart(2,'0')}</span><span class="cd-label">m</span></span><span class="cd-sep">:</span><span class="cd-unit"><span class="cd-num">${String(s).padStart(2,'0')}</span><span class="cd-label">s</span></span>`;
  }
}
document.querySelectorAll('[data-countdown]').forEach((el) => new CountdownTimer(el.dataset.countdown, el.id));
