/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
// import './bootstrap';

document.addEventListener('click', event => {
  const btn = event.target.closest('nav .toggle');
  if (!btn) {
    return;
  }

  event.target.closest('nav').classList.toggle('show');
});

document.addEventListener('click', event => {
  const flash = event.target.closest('.flash');
  if (!flash) {
    return;
  }

  const container = flash.closest('.container');
  flash.remove();
  if (container.children.length === 0) {
    container.remove();
  }
});
