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
        const params = new URLSearchParams(window.location.search);
        var username = params.get('username');
        var partieTerminee = localStorage.getItem('partieTerminee') === 'true';
        function envoyerScore(username, score) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'enregistrer_score.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    console.log('Score enregistré avec succès !');
                }
            };
            xhr.send('username=' + encodeURIComponent(username) + '&score=' + encodeURIComponent(score));
        }
        if (localStorage.getItem('tempsRestant') === null) {
            localStorage.setItem('tempsRestant', 120); // Initialiser le temps de jeu à 120 secondes
            partieTerminee = false; // Initialiser la partie comme non terminée
        }
        
        document.getElementById('inputText').focus();

        var timerDisplay = document.getElementById('timer');
        var seconds = localStorage.getItem('tempsRestant');

        var countdown = setInterval(function() {
            console.log(partieTerminee);
            if (!partieTerminee){
                seconds--;
                console.log("gros cacac mou");
                localStorage.setItem('tempsRestant',seconds);
                if(seconds < 0){
                    seconds = 0;
                }
                document.getElementById('timer').textContent = "Temps Restant: "+ seconds +" secondes";

                if (seconds <= 0) {
                    clearInterval(countdown);
                    afficherFin(parseInt(localStorage.getItem('score2')) || 0);
                    localStorage.setItem('partieTerminee', true);
                    document.getElementById('inputText').disabled = true;
                    document.getElementById('okButton').disabled = true;
                    envoyerScore(username,parseInt(localStorage.getItem('score2'))|| 0);
            }
            }
        }, 1000);


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

        var score = localStorage.getItem('score2') ? parseInt(localStorage.getItem('score2')) : 0;
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
            if (!partieTerminee) {
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
                    updateScore(-1);
                }
            }
            
        });

        document.getElementById('inputText').addEventListener('keypress', function(event) {
            if (event.key === 'Enter' && !partieTerminee) {
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
                    updateScore(-1);
                }
            }
        });

        function updateScore(value) {
                if (value !== undefined) {
                    score += value;
                    localStorage.setItem('score2', score);
                }
                document.getElementById('score').textContent = "Score: " + score;
            }

            function afficherFin(score) {
                document.getElementById('endScore').textContent = score;
                document.getElementById('endScreen').classList.remove('hidden');
            }
        
        function verifierFin() {
                var seconds = localStorage.getItem('tempsRestant');
                if (seconds < 0) {
                    afficherFin(parseInt(localStorage.getItem('score2')) || 0);
                    localStorage.setItem('partieTerminee','true');
                    partieTerminee = true;
                    document.getElementById('inputText').disabled = true;
                    document.getElementById('okButton').disabled = true;
                }
            }
        verifierFin();

        document.getElementById('menuButton').addEventListener('click', function() {
            window.location.href = "menu.html"; 
        });

        document.getElementById('replayButton').addEventListener('click', function() {
            localStorage.setItem('partieTerminee','false');
            localStorage.setItem('tempsRestant',60);
            localStorage.setItem('score2', 0);
            window.location.reload(); 
        });

    };
    
</script>
<p id="timer" class="timer"><script>
    var tmps = localStorage.getItem('tempsRestant')
    if(tmps < 0){
        tmps = 0;
    }
    document.getElementById('timer').textContent = "Temps Restant: "+ tmps +" secondes";
 </script></p>
<div class=milieu>
<canvas id="canvas" class="skin"></canvas>
    <input id="inputText" class="inp" type="text">
    <button id="okButton" class="sub ok">OK</button>
    <p id="messageText" class="message"></p>
    <div class="score" id="score">Score: </div>
    <div id="endScreen" class="end-screen hidden">
        <p>Votre score : <span id="endScore"></span></p>
        <button id="menuButton" class="edn">Menu</button>
        <button id="replayButton" class="end">Rejouer</button>
    </div>
</div>

<p class=fin>By Dieu & Barty Poluip</p>

</body>
</html>
