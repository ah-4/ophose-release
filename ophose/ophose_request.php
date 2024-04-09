<?php

use Ophose\Cookie;

include_once(__DIR__ . "/app/request/security.php");
?>
<html>

    <head>

        <script src="/@dep/jquery.js"></script>
        <?php if(CONFIG["production_mode"]) { ?>
            <script src="/ophose.js"></script>
            <script src="/app.js"></script>
        <?php } else { ?>
            <?php
            include_once(ROOT . '/env/oph/src/commands/build-ophose.php');
            foreach(JS_ORDER as $file) {
                echo "<script src='/@ojs/" . $file . "'></script>";
            }
            ?>
            <script src="/@component/Base.js"></script>
        <?php } ?>

        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">
        <meta name="csrf-token" content="<?php echo CSRF_TOKEN; ?>">

        <script>
        window.addEventListener("load", () => {
            route.go("<?php echo FULL_REQUEST_HTTP_URL; ?>");
        });
        </script>

        <title><?php echo CONFIG["name"] ?></title>
    </head>

    <body>
        <noscript>
            <main
                style="width: 100vw; height: 100vh; position: fixed; z-index: 1000; background-color: black; top: 0; left: 0;">
                <h1
                    style="color: white; font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; text-align: center;">
                    JavaScript is required to run this application.</h1>
            </main>
        </noscript>
    </body>

</html>