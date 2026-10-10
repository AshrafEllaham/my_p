<script src="{{ asset('assets/js/jquery.js') }}"></script>
<script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
<script>
    window.adminValidationMessages = @json(__('admin.form_validation'));
</script>
<script src="{{ asset('assets/js/admin-form-validation.js') }}"></script>
<script src="{{ asset('assets/js/bundle/dropify.bundle.js') }}"></script>
@vite('resources/js/admin.js')
