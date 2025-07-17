<div class="preloader clock text-center" id="preloader">
    <div class="queraLoader">
        <div class="loaderO">
            <span>L</span>
            <span>I</span>
            <span>G</span>
            <span>H</span>
            <span>T</span>
            <span>H</span>
            <span>O</span>
            <span>U</span>
            <span>S</span>
            <span>E</span>
        </div>
    </div>
</div>

<script>
    // Hide preloader when page is loaded
    window.addEventListener('load', function() {
        const preloader = document.getElementById('preloader');
        if (preloader) {
            preloader.style.opacity = '0';
            setTimeout(function() {
                preloader.style.display = 'none';
            }, 800);
        }
    });
</script>