@php
$containerFooter = ($configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
@endphp

<!-- Footer -->
<footer class="content-footer footer bg-footer-theme">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
      <div class="text-body mb-2 mb-md-0">
        © <script>document.write(new Date().getFullYear())</script>
        <b>Box Social.</b> All Rights Reserved.
      </div>
      
    </div>
  </div>
</footer>
<!--/ Footer -->
