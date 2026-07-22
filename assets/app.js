document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('[data-event-form]');
  if (form) {
    const steps = [...form.querySelectorAll('[data-step-panel]')];
    const dots = [...document.querySelectorAll('[data-step-dot]')];
    let current = 0;
    const show = (index) => {
      current = Math.max(0, Math.min(index, steps.length - 1));
      steps.forEach((step, i) => step.hidden = i !== current);
      dots.forEach((dot, i) => {
        dot.classList.toggle('step-primary', i <= current);
        if (i === current) dot.setAttribute('aria-current', 'step'); else dot.removeAttribute('aria-current');
      });
      steps[current].querySelector('input,select,textarea')?.focus({preventScroll: true});
      document.querySelector('#new-event')?.scrollIntoView({behavior: 'smooth', block: 'start'});
    };
    form.addEventListener('click', (event) => {
      const next = event.target.closest('[data-next]');
      const prev = event.target.closest('[data-prev]');
      if (prev) show(current - 1);
      if (next) {
        const required = [...steps[current].querySelectorAll('[required]')];
        const invalid = required.find(el => !el.reportValidity());
        if (!invalid) show(current + 1);
      }
    });
    form.addEventListener('input', () => {
      const data = {};
      new FormData(form).forEach((value, key) => { if (key !== 'csrf' && key !== 'password') data[key] = value; });
      localStorage.setItem('kokuchikun-draft', JSON.stringify(data));
    });
    try {
      const draft = JSON.parse(localStorage.getItem('kokuchikun-draft') || '{}');
      Object.entries(draft).forEach(([key, value]) => {
        const field = form.elements.namedItem(key);
        if (field && !field.value) field.value = value;
      });
    } catch (_) {}
    show(0);
  }

  document.querySelectorAll('[data-copy]').forEach(button => {
    button.addEventListener('click', async () => {
      await navigator.clipboard.writeText(button.dataset.copy);
      const before = button.textContent;
      button.textContent = 'コピーしました！';
      setTimeout(() => button.textContent = before, 1800);
    });
  });

  const online = document.querySelector('#format');
  const locationFields = document.querySelector('#location-fields');
  const toggleLocation = () => locationFields?.classList.toggle('opacity-50', online?.value === 'online');
  online?.addEventListener('change', toggleLocation);
  toggleLocation();

  setTimeout(() => document.querySelector('.toast')?.remove(), 5000);
});
