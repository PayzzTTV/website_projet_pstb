const container = document.querySelector('.container');
container.addEventListener('click', () => {
  container.classList.add('active');
});
container.addEventListener('animationend', () => {
  container.classList.remove('active');
});