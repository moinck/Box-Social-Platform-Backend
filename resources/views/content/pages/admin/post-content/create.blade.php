@extends('layouts/layoutMaster')

@section('title', 'Create Post Content')

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
                    <h4 class="card-title mb-0">Create Post Content</h4>
                </div>
                <div class="card-body mt-2">
                    <form id="create-post-content-form" action="{{ route('post-content.store') }}" class="row g-5"
                        method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- post title --}}
                        <div class="col-12 col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="post_title" name="post_title" class="form-control"
                                    placeholder="Post Title" />
                                <label for="post_title">Post Title</label>
                            </div>
                        </div>

                        {{-- category select --}}
                        <div class="col-12 col-md-6">
                            <div class="form-floating form-floating-outline">
                                <select name="post_category" id="post_category" class="form-select" data-choices
                                    data-choices-search-false>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <label for="post_category">Post Category</label>
                            </div>
                        </div>

                        {{-- sub-category select --}}
                        <div class="col-12 col-md-12 d-none" id="selectSubCategory-div">
                            <div class="form-floating form-floating-outline">
                                <select name="post_sub_category" id="post_sub_category" class="form-select" data-choices
                                    data-choices-search-false>
                                    <option value="">Select Subcategory</option>
                                </select>
                                <label for="post_sub_category">Post Subcategory</label>
                            </div>
                        </div>

                        {{-- quill text description --}}
                        <div class="col-12">
                            <div class="text-end">
                                <p class="mb-1 text-secondary">Add Tags for dynamic Name and Phone</p>
                            </div>
                            <div id="post_description">{{ old('post_description') }}</div>
                            <input type="hidden" name="post_description" id="hiddenPostDescription"
                                value="{{ old('post_description') }}" />
                        </div>

                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="warning_message" name="warning_message" class="form-control"
                                    placeholder="Warning Message" />
                                <label for="warning_message">Warning Message</label>
                            </div>
                        </div>
                        <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                            <button type="submit" class="btn btn-primary">Create</button>
                            <button type="reset" class="btn btn-outline-secondary" id="cancelCreatePostContentBtn">
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
                [
                    {'insert-name': 'Name'},
                    {'insert-phone': 'Phone'},
                    {'insert-website': 'Website'},
                ],
                ['clean']
            ];

            const createPostDescription = new Quill('#post_description', {
                bounds: '#post_description',
                placeholder: 'Type Something...',
                modules: {
                    formula: true,
                    toolbar: {
                        container: fullToolbar,
                        handlers: {
                            'insert-name': function() {
                                const cursorPosition = this.quill.getSelection().index;
                                this.quill.insertText(cursorPosition, '|name|');
                                this.quill.setSelection(cursorPosition + 6);
                            },
                            'insert-phone': function() {
                                const cursorPosition = this.quill.getSelection().index;
                                this.quill.insertText(cursorPosition, '|phone|');
                                this.quill.setSelection(cursorPosition + 7);
                            },
                            'insert-website': function() {
                                const cursorPosition = this.quill.getSelection().index;
                                this.quill.insertText(cursorPosition, '|website|');
                                this.quill.setSelection(cursorPosition + 13);
                            }
                        }
                    }
                },
                theme: 'snow'
            });
            $('.ql-insert-name').attr('title', 'Click to insert Name');
            $('.ql-insert-phone').attr('title', 'Click to insert Phone');
            $('.ql-insert-website').attr('title', 'Click to insert Website');
            // update hidden post description
            createPostDescription.on('text-change', function() {
                // $('#hiddenPostDescription').val(createPostDescription.root.innerHTML);
                $('.ql-editor').hasClass('ql-blank') ?
                    $('#hiddenPostDescription').val('') :
                    $('#hiddenPostDescription').val(createPostDescription.root.innerHTML);
                validator.revalidateField('post_description');
            });

            // cancel create post content
            $('#cancelCreatePostContentBtn').click(function() {
                window.location.href = '{{ route('post-content') }}';
            });

            // profile form validation
            const formValidationExamples = document.getElementById('create-post-content-form');
            const validator = FormValidation.formValidation(formValidationExamples, {
                fields: {
                    post_title: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter post title'
                            }
                        }
                    },
                    post_category: {
                        validators: {
                            notEmpty: {
                                message: 'Please select category'
                            }
                        }
                    },
                    post_description: {
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
                            if (['post_title', 'post_category', 'post_description', ].includes(
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
                $('#create-post-content-form').submit();
            });
            // -----------------------------------------------------

            // post category change event
            $('#post_category').change(function() {
                var category_id = $(this).val();
                if (category_id.length == 0) {
                    $('#post_sub_category').html('<option value="">Select Subcategory</option>');
                    $('#selectSubCategory-div').addClass('d-none');
                    validator.revalidateField('post_sub_category');
                    validator.removeField(`post_sub_category`);
                    return;
                }
                $.ajax({
                    url: '{{ route('post-content.sub-category.get.data') }}',
                    type: 'GET',
                    data: {
                        category_id: category_id
                    },
                    beforeSend: function() {
                        showBSPLoader();
                    },
                    complete: function() {
                        hideBSPLoader();
                    },
                    success: function(data) {
                        if (data.success) {
                            var responseData = data.data;
                            var option = '';
                            option += '<option value="">Select Subcategory</option>';
                            responseData.forEach(function(item) {
                                option += '<option value="' + item.id + '">' + item
                                    .name + '</option>';
                            });
                            $('#post_sub_category').html(option);
                            $('#selectSubCategory-div').removeClass('d-none');

                            validator.revalidateField('post_sub_category');
                            validator.addField(`post_sub_category`, {
                                validators: {
                                    notEmpty: {
                                        message: 'Subcategory is required'
                                    }
                                }
                            });

                        } else {
                            $('#post_sub_category').html(
                                '<option value="">Select Subcategory</option>');
                            $('#selectSubCategory-div').addClass('d-none');
                            validator.revalidateField('post_sub_category');
                            // validator.removeField(`post_sub_category`);
                        }
                    }
                });
            });
        });
    </script>
@endsection
