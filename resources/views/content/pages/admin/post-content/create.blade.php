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
                    <h4 class="card-title mb-0">Create Text Post</h4>
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

                        {{-- Category multi-checkbox dropdown --}}
                        <div class="col-12 col-md-6 dropdown-container">
                        
                        <button id="categoryDropdown" type="button" class="btn btn-outline-secondary w-100 text-start dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span id="selectedCategories">Choose</span>
                        </button>
                        <label for="categoryDropdown" class="dropdown-label">Select Categories</label>
                        <ul class="dropdown-menu w-100" id="categoryMenu">
                            @foreach($categories as $category)
                            <li>
                                <label class="dropdown-item">
                                    <input type="checkbox" class="category-checkbox" value="{{ $category->id }}"> {{ $category->name }}
                                </label>
                            </li>
                            @endforeach
                        </ul>
                        <input type="hidden" name="post_category[]" id="selectedCategoryValues">
                        </div>

                        {{-- Subcategory multi-checkbox dropdown --}}
                        <div class="dropdown-container mt-3 d-none" id="subcategoryContainer">
                        <label for="subcategoryDropdown" class="dropdown-label">Select Subcategories</label>
                        <button id="subcategoryDropdown" type="button" class="btn btn-outline-secondary w-100 text-start dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span id="selectedSubcategories">Choose</span>
                        </button>
                        <ul class="dropdown-menu w-100" id="subcategoryMenu"></ul>
                        <input type="hidden" name="post_sub_category[]" id="selectedSubcategoryValues">
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
                            <button type="submit" id="createPostContentBtn" class="btn btn-primary">Create</button>
                            <button type="reset" class="btn btn-outline-secondary" id="cancelCreatePostContentBtn">
                                Cancel
                            </button>
                        </div>
                        <input type="hidden" id="isValidate" value="0" readonly>
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
                    {'insert-email': 'Email'},
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
                            'insert-email': function() {
                                const cursorPosition = this.quill.getSelection().index;
                                this.quill.insertText(cursorPosition, '|email|');
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
            $('.ql-insert-email').attr('title', 'Click to insert Email');
            $('.ql-insert-website').attr('title', 'Click to insert Website');
            // update hidden post description
            createPostDescription.on('text-change', function() {
                // $('#hiddenPostDescription').val(createPostDescription.root.innerHTML);
                $('.ql-editor').hasClass('ql-blank') ?
                    $('#hiddenPostDescription').val('') :
                    $('#hiddenPostDescription').val(createPostDescription.root.innerHTML);
                if ($('.ql-editor').hasClass('ql-blank')) {
                    $('.ql-toolbar').css("border","2px solid #ff4d49")
                    // on container remove top border
                    $('.ql-container').css("border","2px solid #ff4d49")
                    $('.ql-container').css("border-top","none")
                }else{
                    $('.ql-toolbar').css("border",".0625rem solid #c8ced1")
                    $('.ql-container').css("border",".0625rem solid #c8ced1")
                }
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
            
            $(document).on('click','#createPostContentBtn', function () {
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

            $(document).on('change', '.category-checkbox', function() {
                let selected = [];
                $('.category-checkbox:checked').each(function() {
                    selected.push($(this).val());
                });

                // Update hidden input + label
                $('#selectedCategoryValues').val(selected);
                $('#selectedCategories').text(selected.length ? selected.length + ' selected' : 'Choose');

                if (selected.length === 0) {
                    $('#subcategoryContainer').addClass('d-none');
                    return;
                }

                // Fetch subcategories dynamically
                $.ajax({
                url: '{{ route('post-content.sub-category.get.data') }}',
                type: 'GET',
                data: { category_ids: selected }, 
               
                beforeSend: showBSPLoader,
                complete: hideBSPLoader,
                success: function(data) {
                    if (data.success && data.data.length) {
                        let html = '';
                        data.data.forEach(item => {
                            html += `
                                <li>
                                    <label class="dropdown-item">
                                        <input type="checkbox" class="subcategory-checkbox" value="${item.id}"> ${item.name}
                                    </label>
                                </li>`;
                        });
                        $('#subcategoryMenu').html(html);
                        $('#subcategoryContainer').removeClass('d-none');
                    } else {
                        $('#subcategoryContainer').addClass('d-none');
                        $('#subcategoryMenu').empty();
                    }
                },
                error: function(err) {
                    console.log('AJAX Error:', err);
                }
            });

            });

            $(document).on('change', '.subcategory-checkbox', function() {
                let selected = [];
                $('.subcategory-checkbox:checked').each(function() {
                    selected.push($(this).val());
                });
                $('#selectedSubcategoryValues').val(selected);
                $('#selectedSubcategories').text(selected.length ? selected.length + ' selected' : 'Choose');
            });

        });
    </script>
    <style>
        #create-post-content-form .dropdown-container {
            position: relative;
        }
        #create-post-content-form .dropdown-container button#categoryDropdown, #create-post-content-form .dropdown-container button#subcategoryDropdown {
            padding: calc(.8555rem - 1px) calc(1rem - 1px);
            height: 3.0000625rem;
            min-height: 3.0000625rem;
            line-height: 1.375;
            background-color: #ffffff !important;
            border-color: #c8ced1 !important;
            justify-content: space-between;
        }
        

        #create-post-content-form .dropdown-container button#categoryDropdown + label, #create-post-content-form .dropdown-container button#subcategoryDropdown + label {
            color: #f4d106 !important;
            top: 20px !important;
            position: absolute;
            left: 0;
            font-size: 12px;
            padding: 0 10px;
            margin-left: 15px;
            opacity: 0;
            transition: all 0.5s ease-in-out;
        }
        #create-post-content-form .dropdown-container button#categoryDropdown:focus + label, #create-post-content-form .dropdown-container button#subcategoryDropdown:focus + label {
            top: -10px !important;
            background-color: #ffffff;
            opacity: 1;
            transition: all 0.5s ease-in-out;
        }
        #create-post-content-form .dropdown-container button#categoryDropdown:focus, #create-post-content-form .dropdown-container button#subcategoryDropdown:focus {
            border-color: #f4d106 !important;
        }
        #create-post-content-form .dropdown-container {
        
            position: relative;
        
        }
        
        #create-post-content-form .dropdown-container button#categoryDropdown {        
            padding: calc(.8555rem - 1px) calc(1rem - 1px);        
            height: 3.0000625rem;        
            min-height: 3.0000625rem;        
            line-height: 1.375;        
            background-color: #ffffff !important;        
            border-color: #c8ced1 !important;        
            justify-content: space-between;
        }
        
        #create-post-content-form .dropdown-container button#categoryDropdown + label {
            color: #f4d106 !important;
            top: 20px !important;
            position: absolute;
            left: 0;
            font-size: 12px;
            padding: 0 10px;
            margin-left: 15px;
            opacity: 0;
            transition: all 0.5s ease-in-out;
        }
        #create-post-content-form .dropdown-container button#categoryDropdown:focus + label {
            top: -10px !important;
            background-color: #ffffff;
            opacity: 1;
            transition: all 0.5s ease-in-out;
        }
        #create-post-content-form .dropdown-container button#categoryDropdown:focus {
            border-color: #f4d106 !important;
        }
    </style>
@endsection
