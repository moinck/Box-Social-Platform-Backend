<!-- BEGIN: Vendor JS-->

@vite([
    'resources/assets/vendor/libs/jquery/jquery.js',
    'resources/assets/vendor/libs/popper/popper.js',
    'resources/assets/vendor/js/bootstrap.js',
    'resources/assets/vendor/libs/node-waves/node-waves.js',
    'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
    'resources/assets/vendor/libs/hammer/hammer.js',
    'resources/assets/vendor/libs/typeahead-js/typeahead.js',
    'resources/assets/vendor/js/menu.js',
    'resources/assets/vendor/libs/toastr/toastr.js'
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
        // remove menu customize button
        document.querySelector('#template-customizer')?.remove();

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
                confirmButton: `btn btn-${type === 'success' ? 'primary' : 'danger'}`
            }
        });
    }
</script>
@if (session()->has('warning'))
    <script>
        setTimeout(() => {
            hideBSPLoader();
            showSweetAlert('warning', 'Warning', '{{ session()->get('warning') }}');
        }, 100);
    </script>
@endif
@if (session()->has('success'))
    <script>
        setTimeout(() => {
            hideBSPLoader();
            showSweetAlert('success', 'Success', '{{ session()->get('success') }}');
        }, 1000);
    </script>
@endif
@if (session()->has('error'))
    <script>
        setTimeout(() => {
            hideBSPLoader();
            showSweetAlert('error', 'Error', '{{ session()->get('error') }}');
        }, 1000);
    </script>
@endif

<script src="https://js.pusher.com/8.3.0/pusher.min.js"></script>
{{-- listen pusher event --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pusher = new Pusher(
            "{{ config('broadcasting.connections.pusher.key') }}", {
                cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
                forceTLS: true
            }
        );

        const channel = pusher.subscribe('admin-notifications');
        channel.bind('new-notification', function(data) {
            // console.log(data);
            var notificationData = data;

            var title = notificationData.title;
            var message = notificationData.body;
            // showSweetAlert("success", title, message);
            toastr.options = {
                "progressBar": true,
            };
            toastr.info(message, title);

            // check if any user is logged in then mark as read
            if ({{ auth()->check() }}) {
                $.ajax({
                    url: '/notification/mark-as-read',
                    type: 'POST',
                    data: {
                        id: notificationData.id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });
            }
        });
    });
</script>

<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->
