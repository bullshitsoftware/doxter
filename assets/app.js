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

document.querySelector('nav .toggle').addEventListener('click', event => {
  event.currentTarget.closest('nav').classList.toggle('show');
});

document.querySelector('.flash').addEventListener('click', event => {
  const container = event.currentTarget.closest('.container');
  event.currentTarget.remove();
  if (container.children.length === 0) {
    container.remove();
  }
});
