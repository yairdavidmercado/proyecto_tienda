document.addEventListener('DOMContentLoaded', () => {
  const alerts = document.querySelectorAll('.alert[data-auto-close="true"]');
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.classList.add('fade');
      alert.classList.remove('show');
    }, 3500);
  });
});
