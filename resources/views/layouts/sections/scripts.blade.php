<!-- BEGIN: Vendor JS-->

@vite([
  'resources/assets/vendor/libs/jquery/jquery.js',
  'resources/assets/vendor/libs/popper/popper.js',
  'resources/assets/vendor/js/bootstrap.js',
  'resources/assets/vendor/libs/node-waves/node-waves.js',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
  'resources/assets/vendor/libs/hammer/hammer.js',
  'resources/assets/vendor/libs/typeahead-js/typeahead.js',
  'resources/assets/vendor/js/menu.js'
])

@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])

<!-- END: Theme JS-->
<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->
{{-- common js --}}
<script>
    // Wait for the DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        if (!document.getElementById('box-loader')) {
            const loaderHtml = `
                <div id="box-loader" style="display: none;">
                    <div class="spinner-container">
                        <div class="spinner"></div>
                        <div class="logo-content">
                            <img src="{{ asset('assets/img/Box-media-logo.svg') }}" alt="Box-social Logo" class="loader-logo">
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', loaderHtml);
        }
    });

    // Function to show the loader
    function showBSPLoader() {
        const loader = document.getElementById('box-loader');
        if (loader) {
            loader.style.display = 'flex';
        } else {
            console.warn('Box Loader element not found. Make sure the page is fully loaded.');
        }
    }

    // Function to hide the loader
    function hideBSPLoader() {
        const loader = document.getElementById('box-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }
    
    // show sweet alert function
    function showSweetAlert(type, title, text) {
        Swal.fire({
            icon: type,
            title: title,
            text: text,
            customClass: {
                confirmButton: `btn btn-${type === 'success' ? 'success' : 'danger'}`
            }
        });
    }
</script>
<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->
