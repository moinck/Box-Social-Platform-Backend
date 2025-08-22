@extends('layouts/layoutMaster')

@section('title', 'Edit Cookie Policy')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-select-bs5/select.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-fixedcolumns-bs5/fixedcolumns.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.scss',
        'resources/assets/vendor/libs/@form-validation/form-validation.scss',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
        'resources/assets/vendor/libs/quill/typography.scss',
        'resources/assets/vendor/libs/quill/katex.scss',
        'resources/assets/vendor/libs/quill/editor.scss'
    ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/auto-focus.js',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/vendor/libs/quill/katex.js',
        'resources/assets/vendor/libs/quill/quill.js'
    ])
@endsection

@section('content')
    <div class="col-12">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 card mb-6">
                <div class="card-header">
                    <h4 class="card-title mb-0">@if($cookiePolicy) Edit @else Add @endif Cookie Policy</h4>
                </div>
                <div class="card-body mt-2">
                    <form id="edit-privacy-policy-form" action="{{ route('cookie-policy.save') }}" class="row g-5"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="cookie_policy_id" value="{{ $cookiePolicy ? $cookiePolicy->id : '' }}" />

                        {{-- title --}}
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="title" name="title" class="form-control"
                                    placeholder="Title" value="{{ $cookiePolicy ? $cookiePolicy->title : '' }}" />
                                <label for="title">Title</label>
                            </div>
                        </div>

                        {{-- quill text description --}}
                        <div class="col-12">
                            <div id="cookie_policy_description">{!! $cookiePolicy ? $cookiePolicy->description : '' !!}</div>
                            <input type="hidden" name="cookie_policy_description" id="hiddenCookiePolicyDescription"
                                value="{{ $cookiePolicy ? $cookiePolicy->description : '' }}" />
                        </div>
                        <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                            <button type="submit" class="btn btn-primary">{{ $cookiePolicy ? 'Update' : 'Create' }}</button>
                            <button type="reset" class="btn btn-outline-secondary" id="cancelEditTermsConditionBtn">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        // const fullEditor = new Quill('#post_description', {
        //     bounds: '#post_description',
        //     placeholder: 'Type Something...',
        //     modules: {
        //         formula: true,
        //         toolbar: fullToolbar
        //     },
        //     theme: 'snow'
        // });

        $(document).ready(function() {
            const fullToolbar = [
                [{
                        font: []
                    },
                    {
                        size: []
                    }
                ],
                ['bold', 'italic', 'underline', 'strike'],
                [{
                        color: []
                    },
                    {
                        background: []
                    }
                ],
                [{
                        script: 'super'
                    },
                    {
                        script: 'sub'
                    }
                ],
                [{
                        header: '1'
                    },
                    {
                        header: '2'
                    },
                    'blockquote',
                    'code-block'
                ],
                [{
                        list: 'ordered'
                    },
                    {
                        list: 'bullet'
                    },
                    {
                        indent: '-1'
                    },
                    {
                        indent: '+1'
                    }
                ],
                [{
                    direction: 'rtl'
                }],
                ['link', 'image', 'video', 'formula'],
                ['clean']
            ];

            const createCookiePpolicyDescription = new Quill('#cookie_policy_description', {
                bounds: '#cookie_policy_description',
                placeholder: 'Type Something...',
                modules: {
                    formula: true,
                    toolbar: fullToolbar
                },
                theme: 'snow'
            });
            // update hidden post description
            createCookiePpolicyDescription.on('text-change', function() {
                // $('#hiddenPostDescription').val(createPostDescription.root.innerHTML);
                $('.ql-editor').hasClass('ql-blank') ?
                    $('#hiddenCookiePolicyDescription').val('') :
                    $('#hiddenCookiePolicyDescription').val(createCookiePpolicyDescription.root.innerHTML);
                validator.revalidateField('cookie_policy_description');
            });

            // cancel create post content
            $('#cancelCreateTermsConditionBtn').click(function() {
            });

            // profile form validation
            const formValidationExamples = document.getElementById('edit-privacy-policy-form');
            const validator = FormValidation.formValidation(formValidationExamples, {
                fields: {
                    title: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter title'
                            }
                        }
                    },
                    cookie_policy_description: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter description'
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        eleValidClass: '',
                        rowSelector: function(field, ele) {
                            if (['title', 'cookie_policy_description'].includes(
                                    field)) {
                                return '.col-12';
                            }
                            return '.col-12';
                        }
                    }),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                    autoFocus: new FormValidation.plugins.AutoFocus()
                }
            }).on('core.form.valid', function() {
                $('#edit-privacy-policy-form').submit();
            });
            // -----------------------------------------------------
        });
    </script>
@endsection
