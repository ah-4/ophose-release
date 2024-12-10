<?php
use function Ophose\Util\configuration;
include_once(__DIR__ . "/app/request/security.php");
?>
<!DOCTYPE html>
<html>

    <head>

        <meta name="csrf-token" content="<?php echo CSRF_TOKEN; ?>">

        <script src="/@dep/jquery.js"></script>
        <script>
            const project = {
                name: "<?php echo configuration()->get("name") ?>",
                productionMode: <?php echo configuration()->get("production_mod") ? "true" : "false"; ?>,
            };
            window.history.pushState(window.location.pathname, '', window.location.pathname);
        </script>
        <?php if(configuration()->get("production_mode")) { ?>
            <script src="/app.js"></script>
        <?php } else { ?>
            <?php
            include_once(ROOT . '/env/ophose/src/command/build-ophose.php');
            foreach(JS_ORDER as $file) {
                echo "<script src='/@ojs/" . $file . "'></script>";
            }
            ?>
            <script src="/@component/Base.js"></script>
        <?php } ?>

        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">

        <script>
        window.addEventListener("load", () => {
            route.go("<?php echo FULL_REQUEST_HTTP_URL; ?>");
        });
        </script>

        <title><?php echo configuration()->get("name") ?></title>
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