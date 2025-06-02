@extends('layouts/layoutMaster')

@section('title', 'Edit Post Content')

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
                    <h4 class="card-title mb-0">Edit Post Content</h4>
                </div>
                <div class="card-body mt-2">
                    <form id="edit-post-content-form" action="{{ route('post-content.update') }}" class="row g-5"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="post_id" value="{{ $postContent->id }}">
                        <div class="col-12 col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="post_title" name="post_title" class="form-control"
                                    placeholder="Post Title" value="{{ $postContent->title }}" />
                                <label for="post_title">Post Title</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-floating form-floating-outline">
                                <select name="post_category" id="post_category" class="form-select" data-choices
                                    data-choices-search-false>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ $category->id == $postContent->category_id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <label for="post_category">Post Category</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-12 d-none" id="selectSubCategory-edit-div">
                            <div class="form-floating form-floating-outline">
                                <select name="post_content_edit_sub_category" id="post_content_edit_sub_category"
                                    class="form-select" data-choices data-choices-search-false>
                                    <option value="">Select Subcategory</option>
                                </select>
                                <label for="post_content_edit_sub_category">Post Subcategory</label>
                            </div>
                        </div>
                        <div class="col-12">
                            {{-- <div class="form-floating form-floating-outline">
                                <textarea name="post_description" id="post_description" class="form-control"
                                    placeholder="Post Description"></textarea>
                                <label for="post_description">Post Description</label>
                            </div> --}}
                            <div id="post_description">{!! $postContent->description !!}</div>
                            <input type="hidden" name="post_description" id="hiddenPostDescription"
                                value="{{ $postContent->description }}" />
                        </div>
                        <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <button type="reset" class="btn btn-outline-secondary" id="cancelEditPostContentBtn">
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

        $(document).ready(function () {
            var editSubCategoryId = {{ $postContent->sub_category_id ?? 0 }};
            var editCategoryId = {{ $postContent->category_id ?? 0 }};
            if (editCategoryId != 0) {
                GetSubCategory(editCategoryId);
            }

            const fullToolbar = [
                [
                    {
                        font: []
                    },
                    {
                        size: []
                    }
                ],
                ['bold', 'italic', 'underline', 'strike'],
                [
                    {
                        color: []
                    },
                    {
                        background: []
                    }
                ],
                [
                    {
                        script: 'super'
                    },
                    {
                        script: 'sub'
                    }
                ],
                [
                    {
                        header: '1'
                    },
                    {
                        header: '2'
                    },
                    'blockquote',
                    'code-block'
                ],
                [
                    {
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
                [{ direction: 'rtl' }],
                ['link', 'image', 'video', 'formula'],
                ['clean']
            ];
            const editPostDescription = new Quill('#post_description', {
                bounds: '#post_description',
                placeholder: 'Type Something...',
                modules: {
                    formula: true,
                    toolbar: fullToolbar
                },
                theme: 'snow'
            });

            // update hidden post description
            editPostDescription.on('text-change', function () {
                $('#hiddenPostDescription').val(editPostDescription.root.innerHTML);
                validator.revalidateField('post_description');
            });

            // cancel edit post content
            $('#cancelEditPostContentBtn').click(function () {
                window.location.href = '{{ route('post-content') }}';
            });


            // profile form validation
            const formValidationExamples = document.getElementById('edit-post-content-form');
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
                        rowSelector: function (field, ele) {
                            if (['post_title', 'post_category', 'post_description'
                            ].includes(field)) {
                                return '.col-12';
                            }
                            return '.col-12';
                        }
                    }),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                    autoFocus: new FormValidation.plugins.AutoFocus()
                }
            }).on('core.form.valid', function () {
                $('#edit-post-content-form').submit();
            });
            // -----------------------------------------------------

            // post category change event
            $('#post_category').change(function () {
                var category_id = $(this).val();
                if (category_id.length == 0) {
                    $('#post_content_edit_sub_category').html('<option value="">Select Subcategory</option>');
                    $('#selectSubCategory-edit-div').addClass('d-none');
                    validator.revalidateField('post_content_edit_sub_category');
                    validator.removeField(`post_content_edit_sub_category`);
                    return;
                }
                GetSubCategory(category_id);
            });

            // get sub category
            function GetSubCategory(category_id) {
                $.ajax({
                    url: '{{ route('post-content.sub-category.get.data') }}',
                    type: 'GET',
                    data: {
                        category_id: category_id
                    },
                    beforeSend: function () {
                        showBSPLoader();
                    },
                    complete: function () {
                        hideBSPLoader();
                    },
                    success: function (data) {
                        if (data.success) {
                            var responseData = data.data;
                            var option = '';
                            option += '<option value="">Select Subcategory</option>';
                            responseData.forEach(function (item) {
                                if (editSubCategoryId == item.id) {
                                    option += '<option value="' + item.id + '" selected>' + item.name + '</option>';
                                } else {
                                    option += '<option value="' + item.id + '">' + item.name + '</option>';
                                }
                            });
                            $('#post_content_edit_sub_category').html(option);
                            $('#selectSubCategory-edit-div').removeClass('d-none');

                            validator.revalidateField('post_content_edit_sub_category');
                            validator.addField(`post_content_edit_sub_category`, {
                                validators: {
                                    notEmpty: {
                                        message: 'Subcategory is required'
                                    }
                                }
                            });

                        } else {
                            var subCategoryField = $('#post_content_edit_sub_category');
                            if (editSubCategoryId != 0) {
                                validator.revalidateField('post_content_edit_sub_category');
                                validator.removeField(`post_content_edit_sub_category`);
                            }
                            subCategoryField.html('<option value="">Select Subcategory</option>');
                            $('#selectSubCategory-edit-div').addClass('d-none');
                        }
                    }
                });
            }
            // -----------------------------------------------------
        });
    </script>
@endsection