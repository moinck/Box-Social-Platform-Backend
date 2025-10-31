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
                    <h4 class="card-title mb-0">Edit Text Post</h4>
                </div>
                <div class="card-body mt-2">
                    <form id="edit-post-content-form" action="{{ route('post-content.update') }}" class="row g-5"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="post_id" value="{{ $postContent->id }}">

                        {{-- title --}}
                        <div class="col-12 col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="post_title" name="post_title" class="form-control"
                                    placeholder="Post Title" value="{{ $postContent->title }}" />
                                <label for="post_title">Post Title</label>
                            </div>
                        </div>

                        {{-- category: keep single select as before --}}
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

                        {{-- Month multi-checkbox dropdown (initially hidden; shown by JS when needed) --}}
                        @php
                            // Prepare selected months from old input or model (assumes months stored as comma separated string)
                            $selectedMonthsArr = [];
                            if (old('months')) {
                                $selectedMonthsArr = explode(',', old('months'));
                            } elseif (!empty($postContent->months)) {
                                // if months stored as "1,2,3"
                                $selectedMonthsArr = is_array($postContent->months) ? $postContent->months : explode(',', $postContent->months);
                            }
                        @endphp

                        <div class="col-12 col-md-6 dropdown-container d-none" id="monthContainer">
                            <button id="monthDropdown" type="button"
                                class="btn btn-outline-secondary w-100 text-start dropdown-toggle" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <span id="selectedMonths">{{ count($selectedMonthsArr) ? count($selectedMonthsArr) . ' selected' : 'Choose' }}</span>
                            </button>
                            <label for="monthDropdown" class="dropdown-label">Select Month</label>
                            <ul class="dropdown-menu w-100" id="monthMenu">
                                @foreach ($months as $month)
                                    <li>
                                        <label class="dropdown-item">
                                            <input type="checkbox" class="month-checkbox" value="{{ $month->month_number }}"
                                                {{ in_array((string)$month->month_number, array_map('strval', $selectedMonthsArr)) ? 'checked' : '' }}>
                                            {{ $month->month_name }}
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="months" id="selectedMonthValues" value="{{ implode(',', $selectedMonthsArr) }}">
                        </div>

                        {{-- Subcategory multi-checkbox dropdown (will be filled by AJAX) --}}
                        @php
                            $selectedSubcategoryArr = [];
                            if (old('post_sub_category')) {
                                $selectedSubcategoryArr = is_array(old('post_sub_category')) ? old('post_sub_category') : [old('post_sub_category')];
                            } elseif (!empty($postContent->sub_category_id)) {
                                $selectedSubcategoryArr = [$postContent->sub_category_id];
                            }
                        @endphp

                        <div class="col-12 col-md-6 dropdown-container d-none" id="subcategoryContainer">
                            <button id="subcategoryDropdown" type="button"
                                class="btn btn-outline-secondary w-100 text-start dropdown-toggle" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <span id="selectedSubcategories">{{ count($selectedSubcategoryArr) ? count($selectedSubcategoryArr) . ' selected' : 'Choose' }}</span>
                            </button>
                            <label for="subcategoryDropdown" class="dropdown-label">Select Subcategories</label>

                            <ul class="dropdown-menu w-100" id="subcategoryMenu"></ul>
                            <input type="hidden" name="post_sub_category[]" id="selectedSubcategoryValues" value="{{ implode(',', $selectedSubcategoryArr) }}">
                        </div>

                        {{-- quill text description --}}
                        <div class="col-12">
                            <div class="text-end">
                                <p class="mb-1 text-secondary">Add Tags for dynamic Name and Phone</p>
                            </div>
                            <div id="post_description">{!! $postContent->description !!}</div>
                            <input type="hidden" name="post_description" id="hiddenPostDescription"
                                value="{{ $postContent->description }}" />
                        </div>

                        {{-- warning message --}}
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="warning_message" name="warning_message" class="form-control"
                                    placeholder="Warning Message" value="{{ $postContent->warning_message }}" />
                                <label for="warning_message">Warning Message</label>
                            </div>
                        </div>
                        <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                            <button type="submit" id="updatePostContentBtn" class="btn btn-primary">Update</button>
                            <button type="reset" class="btn btn-outline-secondary" id="cancelEditPostContentBtn">
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
        $(document).ready(function () {
            // Grab preselected server-rendered values
            const initialSelectedMonths = ($('#selectedMonthValues').val() || '').split(',').filter(Boolean);
            const initialSelectedSubcategories = ($('#selectedSubcategoryValues').val() || '').split(',').filter(Boolean);
            const editSubCategoryId = initialSelectedSubcategories.length ? initialSelectedSubcategories.map(String) : [];
            const editSingleSubCategoryId = editSubCategoryId.length ? editSubCategoryId[0] : 0;
            const editCategoryId = $('#post_category').val() || 0;

            // Common Quill toolbar (same as Create)
            const fullToolbar = [
                [{ font: [] }, { size: [] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ script: 'super' }, { script: 'sub' }],
                [{ header: '1' }, { header: '2' }, 'blockquote', 'code-block'],
                [{ list: 'ordered' }, { list: 'bullet' }, { indent: '-1' }, { indent: '+1' }],
                [{ direction: 'rtl' }],
                ['link', 'image', 'video', 'formula'],
                [{ 'insert-name': 'Name' }, { 'insert-email': 'Email' }, { 'insert-phone': 'Phone' }, { 'insert-website': 'Website' }],
                ['clean']
            ];

            const editPostDescription = new Quill('#post_description', {
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
            editPostDescription.on('text-change', function () {
                $('.ql-editor').hasClass('ql-blank') ?
                    $('#hiddenPostDescription').val('') :
                    $('#hiddenPostDescription').val(editPostDescription.root.innerHTML);

                if ($('.ql-editor').hasClass('ql-blank')) {
                    $('.ql-toolbar').css("border","2px solid #ff4d49");
                    $('.ql-container').css("border","2px solid #ff4d49");
                    $('.ql-container').css("border-top","none");
                } else {
                    $('.ql-toolbar').css("border",".0625rem solid #c8ced1");
                    $('.ql-container').css("border",".0625rem solid #c8ced1");
                }
                validator.revalidateField('post_description');
            });

            // cancel edit post content
            $('#cancelEditPostContentBtn').click(function () {
                window.location.href = '{{ route('post-content') }}';
            });

            // form validation
            const formValidationExamples = document.getElementById('edit-post-content-form');
            const validator = FormValidation.formValidation(formValidationExamples, {
                fields: {
                    post_title: {
                        validators: { notEmpty: { message: 'Please enter post title' } }
                    },
                    post_category: {
                        validators: { notEmpty: { message: 'Please select category' } }
                    },
                    post_description: {
                        validators: { notEmpty: { message: 'Please enter description' } }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        eleValidClass: '',
                        rowSelector: function (field, ele) {
                            if (['post_title','post_category','post_description'].includes(field)) {
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

            // for validation on quill editor visual
            $(document).on('click','#updatePostContentBtn', function () {
                if ($('.ql-editor').hasClass('ql-blank')) {
                    $('.ql-toolbar').css("border","2px solid #ff4d49");
                    $('.ql-container').css("border","2px solid #ff4d49");
                    $('.ql-container').css("border-top","none");
                } else {
                    $('.ql-toolbar').css("border",".0625rem solid #c8ced1");
                    $('.ql-container').css("border",".0625rem solid #c8ced1");
                }
            });

            // When category changes, or on load, fetch subcategories (and decide whether month should show)
            function fetchSubcategoriesForCategory(categoryIds, monthIds = []) {
                if (!categoryIds || categoryIds.length === 0) {
                    $('#subcategoryMenu').empty();
                    $('#subcategoryContainer').addClass('d-none');
                    $('#monthContainer').addClass('d-none');
                    return;
                }

                $.ajax({
                    url: '{{ route('post-content.sub-category.get.data') }}',
                    type: 'GET',
                    data: {
                        category_id: categoryIds.length === 1 ? categoryIds[0] : categoryIds,
                        category_ids: categoryIds, // send both for flexibility
                        month_ids: monthIds.length ? monthIds : []
                    },
                    beforeSend: showBSPLoader,
                    complete: hideBSPLoader,
                    success: function(response) {
                        console.log('Edit API Response:', response);

                        // Determine categories that require month (preferred from response.with_month)
                        let categoriesWithMonth = [];
                        if (response.with_month) {
                            categoriesWithMonth = response.with_month.map(String);
                        } else if (response.debug && response.debug.with_month) {
                            categoriesWithMonth = response.debug.with_month.map(String);
                        }

                        // Inspect items to see if any subcategory has month info (fallback)
                        let dataHasMonth = false;
                        if (response.data && Array.isArray(response.data)) {
                            for (let i = 0; i < response.data.length; i++) {
                                const item = response.data[i];
                                if (item.hasOwnProperty('month_id') && item.month_id !== null && item.month_id !== '' && item.month_id !== 0) {
                                    dataHasMonth = true;
                                    break;
                                }
                                if (item.hasOwnProperty('month_number') && item.month_number !== null && item.month_number !== '') {
                                    dataHasMonth = true;
                                    break;
                                }
                            }
                        }

                        const hasMonthCategoryFromDebug = categoryIds.some(cat => categoriesWithMonth.includes(String(cat)));
                        const shouldShowMonth = hasMonthCategoryFromDebug || dataHasMonth;

                        // Show or hide month container
                        if (shouldShowMonth || (monthIds && monthIds.length > 0)) {
                            $('#monthContainer').removeClass('d-none');
                        } else {
                            $('#monthContainer').addClass('d-none');
                            $('.month-checkbox').prop('checked', false);
                            $('#selectedMonths').text('Choose');
                            $('#selectedMonthValues').val('');
                        }

                        // Render subcategories if present
                        if (response.success && response.data && response.data.length > 0) {
                            const html = response.data.map(item => {
                                const name = item.name || item.title || 'Unnamed';
                                // mark checked if it is in preselected array
                                const checked = editSubCategoryId.includes(String(item.id)) ? 'checked' : '';
                                return `
                                    <li>
                                        <label class="dropdown-item">
                                            <input type="checkbox" class="subcategory-checkbox" value="${item.id}" ${checked}>
                                            ${name}
                                        </label>
                                    </li>`;
                            }).join('');
                            $('#subcategoryMenu').html(html);
                            $('#subcategoryContainer').removeClass('d-none');

                            // update selected count ui if pre-selected
                            const preSelected = $('.subcategory-checkbox:checked').map(function(){ return $(this).val(); }).get();
                            $('#selectedSubcategoryValues').val(preSelected.join(','));
                            $('#selectedSubcategories').text(preSelected.length ? preSelected.length + ' selected' : 'Choose');
                        } else {
                            // no subcategories returned
                            $('#subcategoryMenu').empty();
                            $('#subcategoryContainer').addClass('d-none');

                            // if month required but none selected - show hint
                            if (shouldShowMonth && (!monthIds || monthIds.length === 0)) {
                                // use alert or nicer UI as you prefer
                                alert('Please select month to view related subcategories');
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Edit API Error:', status, error, xhr && xhr.responseText);
                        $('#subcategoryContainer').addClass('d-none');
                    }
                });
            }

            // On page load, if category exists, call fetch with preselected months
            if (editCategoryId) {
                // convert months to array of strings
                const monthsOnLoad = initialSelectedMonths.slice();
                fetchSubcategoriesForCategory([String(editCategoryId)], monthsOnLoad);
            }

            // When category select changes
            $('#post_category').change(function() {
                const categoryId = $(this).val();
                if (!categoryId) {
                    $('#subcategoryMenu').empty();
                    $('#subcategoryContainer').addClass('d-none');
                    $('#monthContainer').addClass('d-none');
                    return;
                }

                // reset month selection when category changes
                // but keep the month container visible only if backend says so
                $('.month-checkbox').prop('checked', false);
                $('#selectedMonths').text('Choose');
                $('#selectedMonthValues').val('');

                fetchSubcategoriesForCategory([String(categoryId)], []);
            });

            // when user toggles a month checkbox (dynamically loaded from blade markup)
            $(document).on('change', '.month-checkbox', function() {
                const selectedMonths = $('.month-checkbox:checked').map(function(){ return String($(this).val()); }).get();
                $('#selectedMonthValues').val(selectedMonths.join(','));
                $('#selectedMonths').text(selectedMonths.length ? selectedMonths.length + ' selected' : 'Choose');

                // re-fetch subcategories with newly selected months (if category selected)
                const cat = $('#post_category').val();
                if (cat) {
                    fetchSubcategoriesForCategory([String(cat)], selectedMonths);
                }
            });

            // subcategory checkboxes handler
            $(document).on('change', '.subcategory-checkbox', function() {
                const selected = $('.subcategory-checkbox:checked').map(function() { return $(this).val(); }).get();
                $('#selectedSubcategoryValues').val(selected.join(','));
                $('#selectedSubcategories').text(selected.length ? selected.length + ' selected' : 'Choose');
            });

        });
    </script>

    <style>
        /* Keep your styles consistent with Create blade */
        #edit-post-content-form .dropdown-container {
            position: relative;
        }

        #edit-post-content-form .dropdown-container button#categoryDropdown,
        #edit-post-content-form .dropdown-container button#subcategoryDropdown,
        #edit-post-content-form .dropdown-container button#monthDropdown {
            padding: calc(.8555rem - 1px) calc(1rem - 1px);
            height: 3.0000625rem;
            min-height: 3.0000625rem;
            line-height: 1.375;
            background-color: #ffffff !important;
            border-color: #c8ced1 !important;
            justify-content: space-between;
        }

        #edit-post-content-form .dropdown-container button#categoryDropdown+label,
        #edit-post-content-form .dropdown-container button#monthDropdown+label,
        #edit-post-content-form .dropdown-container button#subcategoryDropdown+label {
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

        #edit-post-content-form .dropdown-container button#categoryDropdown:focus+label,
        #edit-post-content-form .dropdown-container button#monthDropdown:focus+label,
        #edit-post-content-form .dropdown-container button#subcategoryDropdown:focus+label {
            top: -10px !important;
            background-color: #ffffff;
            opacity: 1;
            transition: all 0.5s ease-in-out;
        }

        #edit-post-content-form .dropdown-container button#categoryDropdown:focus,
        #edit-post-content-form .dropdown-container button#monthDropdown:focus,
        #edit-post-content-form .dropdown-container button#subcategoryDropdown:focus {
            border-color: #f4d106 !important;
        }
    </style>
@endsection
