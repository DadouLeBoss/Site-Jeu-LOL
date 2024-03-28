<!DOCTYPE html>
<html lang="fr">


<head>
<meta charset="UTF-8">
<title>Blurry Splash-Art Challenge</title>
<link rel="stylesheet" href="./styles/style.css">
</head>

<body>
<a href="menu.html">
            <img class="logo" src="styles/logo.png">
        </a>
<h1 class=titre>Blurry Splash-Art Challenge</h1>
<script>
    // Fonction pour pixeliser une image
    function pixeliserImage(image, pixelisation) {
        var canvas = document.getElementById('canvas');
        var ctx = canvas.getContext('2d');

        // Définir la taille du canvas
        canvas.width = image.width;
        canvas.height = image.height;

        // Dessiner l'image originale sur le canvas
        ctx.drawImage(image, 0, 0);

        // Récupérer les données de l'image
        var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        var pixels = imageData.data;

        // Pixeliser l'image en modifiant les couleurs de chaque pixel
        for (var y = 0; y < canvas.height; y += pixelisation) {
            for (var x = 0; x < canvas.width; x += pixelisation) {
                var index = (y * canvas.width + x) * 4;
                var r = pixels[index];
                var g = pixels[index + 1];
                var b = pixels[index + 2];

                // Appliquer la couleur du pixel aux pixels adjacents
                for (var j = 0; j < pixelisation; j++) {
                    for (var i = 0; i < pixelisation; i++) {
                        var idx = ((y + j) * canvas.width + (x + i)) * 4;
                        pixels[idx] = r;
                        pixels[idx + 1] = g;
                        pixels[idx + 2] = b;
                    }
                }
            }
        }

        // Mettre à jour les données de l'image sur le canvas
        ctx.putImageData(imageData, 0, 0);
    }

    
    window.onload = function() {
        document.getElementById('inputText').focus();

        var img = new Image();
        img.onload = function() {
            transitionPixelisation(img, 100, 1, 300);
        };
        <?php
        $dossi = "splash/";
        $dossier = array("Aatrox","Akali","Ahri");
        
        $perso = $dossier[array_rand($dossier)];
        $dir = $dossi . $perso;
        $files = glob(strtolower($dir).'/' . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        $randomFile = $files[array_rand($files)]; 


        echo 'img.src = "' . $randomFile . '";';
        echo 'var perso = "'.$perso .'"'
        ?> 

        var score = localStorage.getItem('score') ? parseInt(localStorage.getItem('score')) : 0;
        updateScore();

        function transitionPixelisation(image, startPixelisation, endPixelisation, duration) {
            var currentPixelisation = startPixelisation;
            var canvas = document.getElementById('canvas');
            var ctx = canvas.getContext('2d');

            function animate() {
                pixeliserImage(image, currentPixelisation);
                currentPixelisation -= 5;
                if (currentPixelisation >= endPixelisation) {
                    if (currentPixelisation > 40)
                    {setTimeout(animate, duration);}
                    else{setTimeout(animate, duration*8);}
                }
            }
            animate();
        }
        var trouve = false;
        document.getElementById('okButton').addEventListener('click', function() {
            var inputValue = document.getElementById('inputText').value;
            var messageElement = document.getElementById('messageText');
            if (trouve) {
                window.location.reload();
                updateScore(-1);
            }
            if (inputValue.toLowerCase() == perso.toLowerCase()){
                messageElement.textContent = "Bonne Réponse !";
                trouve = true;
                var okElement = document.getElementById('okButton');
                okElement.textContent = "➜";
                updateScore(1);
            } else {
                messageElement.textContent = "Trop Naze ! Gros Noob";
                score = 0;
                updateScore(0);
            }
            
        });

        document.getElementById('inputText').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                var inputValue = document.getElementById('inputText').value;
                var messageElement = document.getElementById('messageText');
                if (trouve) {
                    window.location.reload();
                    updateScore(-1);
                }
                if (inputValue.toLowerCase() == perso.toLowerCase()){
                    messageElement.textContent = "Bonne Réponse !";
                    trouve = true;
                    var okElement = document.getElementById('okButton');
                    okElement.textContent = "➜";
                    updateScore(1);
                } else {
                    messageElement.textContent = "Trop Naze ! Gros Noob";
                    score = 0;
                    updateScore(0);
                }
            }
        });

        function updateScore(value) {
                if (value !== undefined) {
                    score += value;
                    localStorage.setItem('score', score);
                }
                document.getElementById('score').textContent = "Score: " + score;
            }


    };
</script>
<div class=milieu>
<canvas id="canvas" class="skin"></canvas>
    <input id="inputText" class="inp" type="text">
    <button id="okButton" class="sub ok">OK</button>
    <p id="messageText" class="message"></p>
    <div class="score" id="score">Score: </div>
</div>

<p class=fin>By Dieu & Barty Poluip</p>

</body>
</html>
