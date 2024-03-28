const express = require('express')
const app = express()
const fs = require('fs');
const path = require('path');

const http = require("http");
const server = http.createServer(app);

//Hello World line taken from the express website
app.get('/', (req, res)  => res.send('Hello World!'));
app.use(express.static('public'));
app.use((req, res, next) => {
    res.setHeader('Access-Control-Allow-Origin', '*');
    next();
});

const io = require('socket.io')(server, {
    cors: {
        origin: "http://localhost:5500",
        methods: ["GET", "POST"],
        transports: ['websocket', 'polling'],
        credentials: true
    },
    allowEIO3: true
});

const usersMap = new Map();
const RoomsState = new Map();
const RoomNumber = new Map();
const socketRoomMap = new Map();
const ScoreParJoueur = new Map();
let JoueurGagnant = [];

io.on("connection", (socket) => {

    socket.on('create', function(room,username) {
        socket.join(room);
        usersMap.set(socket.id, username);
        socketRoomMap.set(socket.id, room);
        console.log(usersMap);
        if (RoomsState.has(room)){
            socket.emit('gameState', RoomsState.get(room));
        }
        if (RoomNumber.has(room)) {
            RoomNumber.set(room,RoomNumber.get(room) + 1);
        } else {
            RoomNumber.set(room, 1);
        }
    });
    // Pour vider la users map et RoomState
    socket.on('disconnect', () => {
        console.log("User disconnected:", socket.id);
        
        const disconnectedSocketId = socket.id;
        const room = socketRoomMap.get(disconnectedSocketId); // Obtenir le numéro de la salle pour cet utilisateur
        if (room) {
            const participants = RoomNumber.get(room);
            if (participants === 1) {
                RoomNumber.delete(room);
                RoomsState.delete(room);
                console.log(`La salle ${room} est maintenant vide. Suppression de son état.`);
            } else {
                RoomNumber.set(room, participants - 1);
            }
        }
        socketRoomMap.delete(disconnectedSocketId);
        
        usersMap.delete(socket.id);
    });

    socket.on('messageSend', (clientRoom, message) => {
        const username = usersMap.get(socket.id);
        io.to(clientRoom).emit('messageDisplay',username, message,socket.id);
    })

    socket.on('lauchGame',(clientRoom)=>{
        RoomsState.set(clientRoom,true);
        io.to(clientRoom).emit('gameStart',socket.id);
    })

    let imageSelected = null;

    socket.on('ImageDemande', (clientRoom,id) => {
        if (socket.id === id){
        selectRandomImage(clientRoom);
        }

    });

    function selectRandomImage(clientRoom) {
        // Logique pour sélectionner une image aléatoire
        const dossiers = ["aatrox", "akali", "ahri"];
        const dossierAleatoire = dossiers[Math.floor(Math.random() * dossiers.length)];
        const cheminDossier = "public/splash/" + dossierAleatoire;
        const cheminDossier2 = "splash/" + dossierAleatoire;
        const urlImages = cheminDossier.toLowerCase() + '/*.{jpg,jpeg,png,gif}';

        // Requête AJAX pour récupérer la liste des fichiers dans le dossier
        fs.readdir(cheminDossier, (err, fichiers) => {
            if (err) {
                console.error("Erreur lors de la lecture du dossier :", err);
                return;
            }

            // Filtrer les fichiers pour inclure uniquement les images
            const images = fichiers.filter(fichier => {
                const extension = path.extname(fichier).toLowerCase();
                return extension === '.jpg' || extension === '.jpeg' || extension === '.png' || extension === '.gif';
            });

            // Sélectionner aléatoirement une image dans le dossier
            const fichierAleatoire = images[Math.floor(Math.random() * images.length)];
            const fichierComplet = path.join(cheminDossier2, fichierAleatoire);

            // Enregistrer l'image sélectionnée
            imageSelected = fichierComplet;

            // Envoyer le nom de l'image et le nom du dossier au client qui a demandé l'image
            io.to(clientRoom).emit('ImageSelec', fichierComplet, Date.now(), dossierAleatoire);
        });
    }

    socket.on('ScoreUpdateVictoire', (username, clientRoom, id,timeTaken) => {
        if (socket.id === id) {
            if (ScoreParJoueur.has(socket.id)) {
                ScoreParJoueur.set(socket.id, ScoreParJoueur.get(socket.id) + Math.round(300 - timeTaken/100));
            } else {
                ScoreParJoueur.set(socket.id, Math.round(300 - timeTaken/100));
            }
            JoueurGagnant.push(id);

            // Vérifier si tous les joueurs de la salle ont trouvé l'image
            const roomPlayers = Array.from(socketRoomMap.keys()).filter(playerId => socketRoomMap.get(playerId) === clientRoom);
            const winnersCount = JoueurGagnant.filter(playerId => roomPlayers.includes(playerId)).length;
            const totalPlayersCount = RoomNumber.get(clientRoom);
            // Si tous les joueurs de la salle ont trouvé l'image
            if (winnersCount === totalPlayersCount) {
                // Effectuez une action spécifique, comme afficher un message ou passer à la prochaine étape du jeu
                io.to(clientRoom).emit('AllPlayersFoundImage',id);
                JoueurGagnant = JoueurGagnant.filter(playerId => {
                    const playerRoom = socketRoomMap.get(playerId);
                    return playerRoom !== clientRoom;
                });
            }

            // Filtrer les scores pour inclure uniquement ceux des utilisateurs de la salle spécifique
            const roomScores = Array.from(ScoreParJoueur).filter(([userId, _]) => {
                return socketRoomMap.get(userId) === clientRoom;
            });
            // Envoyer les scores filtrés
            io.to(clientRoom).emit('ScoreUpdate', roomScores, Array.from(usersMap),JoueurGagnant);
        }
    });
});



server.listen(5500, () => {
    console.log('Server running on port 5500');
});