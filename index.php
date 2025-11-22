<?php
session_start();

include 'link/header.php';
?>
<!DOCTYPE html>
<html lang="id">

<body style="background-color:#0d1217 !important; color: white !important;">

    <?php include 'bar/navbar.php' ?>

    <?php include 'modal_laporan.php' ?>

    <?php include 'views/carousel.php' ?>

    <?php include 'views/about.php' ?>

    <?php include 'views/berita.php' ?>

    <?php include 'views/data.php' ?>

    <?php include 'views/service.php' ?>


    <a href="#" class="btn btn-primary btn-lg-square back-to-top">
        <i class="bi bi-arrow-up"></i>
    </a>

    <?php include 'link/js.php' ?>

    <script>
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('.back-to-top').fadeIn('slow');
            } else {
                $('.back-to-top').fadeOut('slow');
            }
        });

        $('.back-to-top').click(function() {
            $('html, body').animate({
                scrollTop: 0
            }, 800, 'easeInOutExpo');
            return false;
        });

        $('.counter-value').each(function() {
            $(this).prop('Counter', 0).animate({
                Counter: $(this).text()
            }, {
                duration: 2000,
                easing: 'swing',
                step: function(now) {
                    $(this).text(Math.ceil(now).toLocaleString());
                }
            });
        });

        // Tombol LAPOR SEKARANG dan Layanan Pengaduan
        $('#btnLaporHero, .btn-lapor-hero').click(function() {
            var isLoggedIn = $(this).data('logged-in') == '1';
            if (isLoggedIn) {
                $('#modalLaporan').modal('show');
            } else {
                $('#loginAlertContainer').html('<div class="alert mb-3" style="background: #B45309; color: #ffffff; border-radius: 10px; border-left: 4px solid #FFD700;"><i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Perhatian!</strong> Anda harus login terlebih dahulu untuk melakukan pengaduan.</div>');
                $('#modalLogin').modal('show');
            }
        });
    </script>

</body>

</html>