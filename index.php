<!DOCTYPE html>

<html>

    <head>
        <title>Проверить ИНН</title>
        <meta name="author" content="Никита">

        <meta charset="UTF-8">

        <!-- <link rel="stylesheet" href="/style.css"> -->
    </head>

    <body>
        <form method="post">
            <label for="lname">Введите ИНН:</label><br>
            <input type="text" id="inn" name="inn" value="" maxlength="12"><br><br>
            <input type="submit" value="Проверить">
        </form>
        
        <?php
        include("check.php");
        echo check();
        ?>

    </body>

</html>