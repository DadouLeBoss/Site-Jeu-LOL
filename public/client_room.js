const socket = io.connect("http://localhost:5500/");

const params = new URLSearchParams(window.location.search);
const roomName = params.get('room');
const username = params.get('username');
let JoueurGagnant = [];
document.getElementById('roomNumber').textContent = roomName;

var gameStarted = false;
var imageTrouve = true;
var persoglobal = null;
let imageReceivedTime = null;
// Émettre un événement pour créer une salle avec le nom récupéré
socket.emit('create', roomName,username);
if (gameStarted) {
    lauchBtn.style.display = 'none'; // Masquer le bouton de démarrage si la partie a déjà démarré
}
const switchBtn = document.getElementById('switchButton');
const messageZone = document.getElementById('messageText');

// Pour envoyer les messages au serveur
switchBtn.addEventListener('click', () => {
    const messageText = messageZone.value;
    if (messageText != '') {
        socket.emit('messageSend',roomName,messageText)
        messageZone.value = '';
    }
})
messageZone.addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        const messageText = messageZone.value;
        if (messageText != '') {
            socket.emit('messageSend',roomName,messageText)
            messageZone.value = '';
        }
    }
})
// Pour scroll en bas a chaque message
function scrollToBottom() {
    const chatElement = document.querySelector('.chat');
    chatElement.scrollTop = chatElement.scrollHeight;
}
// Afficher un Message
const chatElement = document.querySelector('.chat');
function ajouterMessage(username,message) {
    const messageElement = document.createElement('p');
    const usernameElement = document.createElement('u');
    usernameElement.textContent = username;
    messageElement.appendChild(usernameElement);
    messageElement.innerText  += ' : ' + message;
    chatElement.appendChild(messageElement);
    scrollToBottom()
}

function ajouterVictoire(username) {
    const messageElement = document.createElement('p');
    messageElement.classList.add('victoire');
    const victoryText = document.createTextNode("Bravo ! ");
    const usernameElement = document.createElement('u');
    usernameElement.textContent = username;
    const victoryText2 = document.createTextNode(" a trouvé le champion");
    
    messageElement.appendChild(victoryText); 
    messageElement.appendChild(usernameElement);
    messageElement.appendChild(victoryText2);
    chatElement.appendChild(messageElement); 
    scrollToBottom()
}
//Recevoir les messages a afficher
socket.on('messageDisplay',(username, messageRecu,id)=> {
    console.log("message bien recu de ", username)
    if (!imageTrouve){
        if (messageRecu.toLowerCase() === persoglobal.toLowerCase()) {
            if (JoueurGagnant.indexOf(id) == -1){
                ajouterVictoire(username);
                const timeTaken = Date.now() - imageReceivedTime;
                socket.emit('ScoreUpdateVictoire',username,roomName,id,timeTaken);
            } else {
                ajouterMessage(username,messageRecu);
            }
        } else {
            ajouterMessage(username,messageRecu);
        }
    } else {
        ajouterMessage(username,messageRecu);
    }
})

// Pour Démarrer la partie
const lauchBtn = document.getElementById('lauchButton');
lauchBtn.addEventListener('click',()=> {
    socket.emit('lauchGame',roomName);
})

socket.on('gameState', (state) => {
    gameStarted = state;
    imageTrouve = false;
    if (gameStarted) {
        lauchBtn.style.display = 'none'; // Masquer le bouton de démarrage si la partie a déjà démarré
    }
});

// Debut de Partie
socket.on('gameStart',(id)=>{
    lauchBtn.style.display = 'none';
    console.log('Debut de la Partie')
    gameStarted = true

    if (gameStarted && imageTrouve) {
        imageTrouve = false;
        socket.emit('ImageDemande', roomName,id);

        console.log("J'ai demandé une Image" + id);
    }
})


socket.on('ImageSelec', (image, timestamp, perso) => {
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    const img = new Image();
    persoglobal = perso;
    imageReceivedTime = Date.now();

    img.onload = function() {
        // Dessiner l'image sur le canvas
        context.drawImage(img, 0, 0, canvas.width, canvas.height);
        const delay = timestamp - Date.now();

        // Démarrer la pixelisation après le délai
        setTimeout(() => {
            transitionPixelisation(img, 100, 1, 300);
        }, delay);
    };

    // Charger l'image
    img.src = image;
        

});

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
var pixelisationInterval;
function transitionPixelisation(image, startPixelisation, endPixelisation, duration) {
    var currentPixelisation = startPixelisation;
    var canvas = document.getElementById('canvas');
    var ctx = canvas.getContext('2d');

    function animate() {
        pixeliserImage(image, currentPixelisation);
        currentPixelisation -= 5;
        if (currentPixelisation >= endPixelisation) {
            if (currentPixelisation > 40) {
                pixelisationInterval = setTimeout(animate, duration);
            } else {
                pixelisationInterval = setTimeout(animate, duration * 8);
            }
        }
    }
    animate();
}

function arreterPixelisation() {
    clearInterval(pixelisationInterval);
}

socket.on('ScoreUpdate', (scoreArray, usersArray,GagnantArray) => {
    JoueurGagnant = GagnantArray;
    const scoresTable = document.getElementById('scoresTable');
    // Effacer le contenu actuel du tableau des scores
    scoresTable.innerHTML = '';
    console.log(scoreArray, usersArray);
    // Vérifier que scoreArray est un tableau
    if (Array.isArray(scoreArray)) {
        // Parcourir chaque entrée dans scoreArray
        scoreArray.forEach(([userId, score]) => {
            // Créer une ligne pour ce score
            const row = document.createElement('div');
            row.classList.add('score-row');

            var username = "Utilisateur inconnu"
            usersArray.forEach(([id,id_name]) => {
                if (userId === id){
                    username = id_name;
                }
            })
            

            // Créer une cellule pour le nom d'utilisateur
            const usernameCell = document.createElement('span');
            usernameCell.classList.add('username-cell');
            usernameCell.textContent = username;
            row.appendChild(usernameCell);

            // Créer une cellule pour le score
            const scoreCell = document.createElement('span');
            scoreCell.classList.add('score-cell');
            scoreCell.textContent = " : "+ score+" points";
            row.appendChild(scoreCell);

            // Ajouter la ligne au tableau des scores
            scoresTable.appendChild(row);
        });
    } else {
        // Si scoreArray n'est pas un tableau valide, afficher un message d'erreur
        const errorMessage = document.createElement('div');
        errorMessage.textContent = "Les données de score ne sont pas valides.";
        scoresTable.appendChild(errorMessage);
    }
});

socket.on('AllPlayersFoundImage',(identifiant)=>{
    arreterPixelisation();
    socket.emit('ImageDemande', roomName,identifiant);
    JoueurGagnant = [];
})