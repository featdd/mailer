export class XhrForm {
  xhrUri = null

  validClassName = 'is-valid'
  errorClassName = 'is-invalid'
  errorMessageTag = 'div'
  errorMessageClassName = 'invalid-feedback'

  /**
   * @param {HTMLFormElement} form
   * @param {object} configuration
   */
  constructor(form, configuration) {
    for (const property in this) {
      if (property in configuration) {
        this[property] = configuration[property];
      }
    }

    if (null === this.xhrUri) {
      this.xhrUri = form.getAttribute('data-uri-xhr');
    }

    this.registerEvents(form);
  }

  /**
   * @param {HTMLFormElement} form
   */
  registerEvents(form) {
    form.addEventListener('submit', event => {
      event.preventDefault();
      this.formSubmit(event.target);
    });

    Object.entries({
      keydown: 'input,textarea',
      change: 'select'
    }).forEach(
      ([eventName, selector]) => form.querySelectorAll(selector).forEach(
        element => element.addEventListener(
          eventName,
          event => this.resetFieldValidationFeedback(event.target)
        )
      )
    );
  }

  /**
   * @param {HTMLInputElement|HTMLSelectElement} field
   */
  resetFieldValidationFeedback(field) {
    const fieldContainer = field.parentNode;
    const errorMessage = fieldContainer.querySelector(this.errorMessageClassName);

    field.classList.remove(this.errorClassName);
    field.classList.remove(this.validClassName);

    if (errorMessage instanceof HTMLElement) {
      fieldContainer.removeChild(errorMessage);
    }
  }

  /**
   * @param {HTMLFormElement} form
   */
  formSubmit(form) {
    const formData = new FormData(form);

    this.formDisable(form);

    fetch(this.xhrUri, { method: 'POST', body: formData })
      .then(
        response => response
          .json()
          .then(
            data => {
              if (true === response.ok) {
                if ('html' in data) {
                  this.handleSuccessResponse(form, data.html);
                }

                return Promise.resolve();
              } else if (false === response.ok && 'errors' in data) {
                this.resetValidationErrors(form);
                this.handleErrorResponse(form, data.errors);
                this.formEnable(form);

                return Promise.resolve();
              }

              return Promise.reject();
            }
          )
          .catch(() => Promise.reject(response))
      )
      .catch(response => this.handleRequestError(response))
  }

  /**
   * @param {HTMLFormElement} form
   * @param {string} replaceHtml
   */
  handleSuccessResponse(form, replaceHtml) {
    const textArea = document.createElement('textarea');
    const replacement = document.createElement('div');

    textArea.innerHTML = replaceHtml;
    replacement.innerHTML = textArea.innerText;

    [...replacement.childNodes].forEach(
      element => form.parentNode.insertBefore(element, form)
    );

    form.parentNode.removeChild(form)
  }

  /**
   * @param {HTMLFormElement} form
   * @param {object} errors
   * @param {string[]} errors.form
   * @param {object} errors.field
   */
  handleErrorResponse(form, errors) {
    if ('form' in errors) {
      form.classList.add(this.errorClassName);
      form.insertBefore(
        this.errorMessageFeedbackElement(errors.form),
        form.firstChild
      );
    }

    if ('field' in errors) {
      /** @param {string[]} messages */
      Object.entries(errors.field).forEach(([fieldName, messages]) => {
        const field = form.querySelector('[name="tx_mailer_form\\[form\\]\\[' + fieldName + '\\]"]');

        if (0 < messages.length) {
          field.classList.add(this.errorClassName);
          field.parentNode.insertBefore(this.errorMessageFeedbackElement(messages), field.nextSibling);
        } else {
          field.classList.add(this.validClassName);
        }
      });
    }
  }

  /**
   * @param {Response} response
   */
  handleRequestError(response) {
    console.error(response.statusText);
    console.debug(response);
  }

  /**
   * @param {HTMLFormElement} form
   */
  resetValidationErrors(form) {
    form.querySelectorAll(':scope > .' + this.errorMessageClassName).forEach(
      formError => formError.parentNode.removeChild(formError)
    );

    form.classList.remove(this.validClassName);
    form.classList.remove(this.errorClassName);
    form.querySelectorAll('.' + this.errorClassName).forEach(field => field.classList.remove(this.errorClassName));
    form.querySelectorAll('.' + this.errorMessageClassName).forEach(errorMessage => errorMessage.parentNode.removeChild(errorMessage));
  }

  /**
   * @param {HTMLFormElement} form
   */
  formDisable(form) {
    form.querySelectorAll('input,textarea,select,button').forEach(field => field.disabled = true);
  }

  /**
   * @param {HTMLFormElement} form
   */
  formEnable(form) {
    form.querySelectorAll('input,textarea,select,button').forEach(field => field.disabled = false);
  }

  /**
   * @param {string[]} messages
   * @returns {HTMLElement}
   */
  errorMessageFeedbackElement(messages) {
    const errorFeedback = document.createElement(this.errorMessageTag);

    errorFeedback.classList.add(this.errorMessageClassName);

    if (1 === messages.length) {
      errorFeedback.innerText = messages.shift();
    } else {
      const messagesList = document.createElement('ul');

      messages.forEach(message => {
        const messageListItem = document.createElement('li');

        messageListItem.innerText = message;

        messagesList.appendChild(messageListItem);
      });

      errorFeedback.appendChild(messagesList);
    }

    return errorFeedback;
  }
}
