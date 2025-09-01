@extends('layouts/layoutMaster')

@section('title', 'Email Content')

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
                    <h4 class="card-title mb-0">@if ($emailContent) Edit @else Create @endif Email Content</h4>
                </div>
                <div class="card-body mt-2">
                    <form id="email-settings-form" action="{{ route('email-settings.save') }}" class="row g-5"
                        method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- post title --}}
                        <div class="col-12 col-md-4">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="title" name="title" class="form-control"
                                    placeholder="Title" value="{{ old('title') ? old('title') : ($emailContent ? $emailContent->title : '') }}"/>
                                <label for="title">Title</label>
                            </div>
                        </div>

                        {{-- category select --}}
                        <div class="col-12 col-md-4">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="subject" name="subject" class="form-control"
                                    placeholder="Subject" value="{{ old('subject') ? old('subject') : ($emailContent ? $emailContent->subject : '') }}"/>
                                <label for="subject">Subject</label>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-4">
                            <div class="form-floating form-floating-outline">
                                <select id="slug" name="slug" class="form-select">
                                    <option value="">Select Email For</option>
                                    @foreach ($emailType as $key => $val)
                                        <option value="{{ $key }}" {{ old('slug') == $key ? 'selected' : ($emailContent && $emailContent->slug == $key ? 'selected' : '') }}>{{ $val }}</option>
                                    @endforeach
                                </select>
                                <label for="slug">Email For</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div id="content">{!! old('content') ? old('content') : ($emailContent ? $emailContent->content : '') !!}</div>
                            <input type="hidden" name="content" id="hiddenEmailContent"
                                value="{{ old('content') ? old('content') : ($emailContent ? $emailContent->content : '') }}" />
                        </div>

                        <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                            <button type="submit" id="createEmailContentBtn" class="btn btn-primary">Save</button>
                            <button type="reset" class="btn btn-outline-secondary" id="cancelEmailContentBtn">
                                Cancel
                            </button>
                        </div>
                        <input type="hidden" name="id" value="@if ($emailContent) {{ $id }} @endif">
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        // const fullEditor = new Quill('#content', {
        //     bounds: '#content',
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

            const createPostDescription = new Quill('#content', {
                bounds: '#content',
                placeholder: 'Type Something...',
                modules: {
                    formula: true,
                    toolbar: {
                        container: fullToolbar,
                        handlers: {
                            
                        }
                    }
                },
                theme: 'snow'
            });

            // update hidden post description
            createPostDescription.on('text-change', function() {
                // $('#hiddenEmailContent').val(createPostDescription.root.innerHTML);
                $('.ql-editor').hasClass('ql-blank') ?
                    $('#hiddenEmailContent').val('') :
                    $('#hiddenEmailContent').val(createPostDescription.root.innerHTML);
                if ($('.ql-editor').hasClass('ql-blank')) {
                    $('.ql-toolbar').css("border","2px solid #ff4d49")
                    // on container remove top border
                    $('.ql-container').css("border","2px solid #ff4d49")
                    $('.ql-container').css("border-top","none")
                }else{
                    $('.ql-toolbar').css("border",".0625rem solid #c8ced1")
                    $('.ql-container').css("border",".0625rem solid #c8ced1")
                }
                validator.revalidateField('content');
            });

            // cancel create post content
            $('#cancelEmailContentBtn').click(function() {
                window.location.href = '{{ route('email-settings') }}';
            });

            // profile form validation
            const formValidationExamples = document.getElementById('email-settings-form');
            const validator = FormValidation.formValidation(formValidationExamples, {
                fields: {
                    title: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter title'
                            }
                        }
                    },
                    subject: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter subject'
                            }
                        }
                    },
                    slug: {
                        validators: {
                            notEmpty: {
                                message: 'Please select email for'
                            }
                        }
                    },
                    content: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter email content'
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        eleValidClass: '',
                        rowSelector: function(field, ele) {
                            if (['title', 'subject', 'slug', 'content', ].includes(
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
                $('#email-settings-form').submit();
            });
            // -----------------------------------------------------

            // for validation on quill editor
            $(document).on('click','#createEmailContentBtn', function () {
                if ($('.ql-editor').hasClass('ql-blank')) {
                    $('.ql-toolbar').css("border","2px solid #ff4d49")
                    // on container remove top border
                    $('.ql-container').css("border","2px solid #ff4d49")
                    $('.ql-container').css("border-top","none")
                }else{
                    $('.ql-toolbar').css("border",".0625rem solid #c8ced1")
                    $('.ql-container').css("border",".0625rem solid #c8ced1")
                }
            });

        });
    </script>
@endsection
