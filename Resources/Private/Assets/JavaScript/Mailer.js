import LazyLoad from 'vanilla-lazyload';

(function () {
  document.addEventListener('DOMContentLoaded', () => new LazyLoad({
    elements_selector: 'form.mailer',
    threshold: 60,
    unobserve_entered: true,
    callback_enter: form => import(/* webpackChunkName: "xhrForm" */ './XhrForm')
      .then(({ XhrForm }) => new XhrForm(form, JSON.parse(form.getAttribute('data-configuration')) ?? {}))
      .catch(error => console.error(error))
  }));
})()
