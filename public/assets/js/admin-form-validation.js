(function ($) {
    'use strict';

    if (!$ || !$.fn.validate) return;

    const messages = window.adminValidationMessages || {};
    const selector = 'form[data-admin-validate]';

    $.extend($.validator.messages, messages);

    $.validator.addMethod('pattern', function (value, element, pattern) {
        return this.optional(element) || new RegExp(`^(?:${pattern})$`).test(value);
    }, messages.pattern);

    $.validator.addMethod('adminFileType', function (_value, element, acceptedTypes) {
        if (!element.files || element.files.length === 0) return true;

        const accepted = acceptedTypes.split(',').map((type) => type.trim().toLowerCase());
        return Array.from(element.files).every((file) => {
            const mimeType = file.type.toLowerCase();
            const extension = `.${file.name.split('.').pop().toLowerCase()}`;

            return accepted.some((type) => type === mimeType
                || type === extension
                || (type.endsWith('/*') && mimeType.startsWith(type.slice(0, -1))));
        });
    }, messages.file);

    const initializeForm = (form) => {
        if (!(form instanceof HTMLFormElement) || !form.matches(selector) || $(form).data('validator')) return;

        $(form).validate({
            ignore: ':hidden:not(.dropify)',
            errorElement: 'p',
            errorClass: 'admin-field__error',
            validClass: 'is-valid',
            errorPlacement(error, element) {
                const field = element.closest('.admin-field');
                const control = field.find('.admin-field__control').last();

                if (control.length) {
                    error.insertAfter(control);
                } else if (field.length) {
                    error.appendTo(field);
                } else {
                    error.insertAfter(element);
                }
            },
            highlight(element) {
                const input = $(element);
                input.addClass('has-error');
                input.closest('.admin-field__control, .dropify-wrapper').addClass('has-error');
            },
            unhighlight(element) {
                const input = $(element);
                input.removeClass('has-error');
                input.closest('.admin-field__control, .dropify-wrapper').removeClass('has-error');
            },
            invalidHandler(_event, validator) {
                if (validator.errorList.length) {
                    validator.errorList[0].element.focus({ preventScroll: true });
                    validator.errorList[0].element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            },
        });

        const password = form.querySelector('[name="password"]');
        const confirmation = form.querySelector('[name="password_confirmation"]');
        const currentPassword = form.querySelector('[name="current_password"]');

        if (password && confirmation) {
            $(confirmation).rules('add', { equalTo: password });
        }

        if (password && currentPassword) {
            $(currentPassword).rules('add', {
                required: () => password.value.length > 0,
            });
        }

        form.querySelectorAll('[pattern]').forEach((element) => {
            $(element).rules('add', { pattern: element.getAttribute('pattern') });
        });

        form.querySelectorAll('input[type="file"][accept]').forEach((element) => {
            $(element).rules('add', { adminFileType: element.getAttribute('accept') });
        });
    };

    document.querySelectorAll(selector).forEach(initializeForm);

    // Modal forms are injected after the initial page load.
    document.addEventListener('submit', (event) => {
        if (!(event.target instanceof HTMLFormElement) || !event.target.matches(selector)) return;

        initializeForm(event.target);
        if (!$(event.target).valid()) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);
})(window.jQuery);
