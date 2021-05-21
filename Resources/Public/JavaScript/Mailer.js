(function (configuration) {
  let mailer = {
    errorClassName: 'errorClassName' in configuration ? configuration.errorClassName : 'is-invalid',
    errorClassParentTargetSelector: 'errorClassParentTargetSelector' in configuration ? configuration.errorClassParentTargetSelector : null,
    errorMessageTargetSelector: 'errorMessageTargetSelector' in configuration ? configuration.errorMessageTargetSelector : null,
    errorMessageTemplate: 'errorMessageTemplate' in configuration ? configuration.errorMessageTemplate : '<div class="invalid-feedback">|</div>',
  };

  // TODO: Implement xhr submit with validaiton
  mailer.init = function () {
    console.debug('Initialize Mailer JavaScript');
    console.debug(this);
  };

  document.addEventListener('DOMContentLoaded', function () {
    try {
      mailer.init();
    } catch (exception) {
      console.error('Mailer Error: ' + exception);
    }
  });
})(typeof mailerConfiguration === 'object' ? mailerConfiguration : {});
