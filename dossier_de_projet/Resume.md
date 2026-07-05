# FitProgress

FitProgress est une application web de suivi de poids et de santé qui permet à ses utilisateurs de suivre leur évolution physique au quotidien. Elle intègre également un système de coaching qui met en relation les utilisateurs avec des coachs sportifs certifiés pour un accompagnement personnalisé.

Présentation générale

Chaque utilisateur enregistre quotidiennement son poids et ses données de santé (poids, alimentation, hydratation, sommeil, humeur, énergie) et visualiser sa progression dans le temps grâce à des graphiques interactifs. L'application lui permet de définir un objectif personnalisé (poids cible) et de suivre son évolution via un tableau de bord complet.

Lors de son inscription, l'utilisateur renseigne sa taille afin de permettre le calcul automatique de son IMC (Indice de Masse Corporelle). 

S'il le souhaite, il peut également choisir un coach parmi la liste des coachs disponibles et certifiés, lui envoyer une demande de suivi et échanger avec lui via une messagerie interne sécurisée. Le coaching est entièrement optionnel, l'application est pleinement fonctionnelle sans coach assigné.

### Fonctionnalités principales

L'application permet à l'utilisateur de :


- suivre son poids quotidien et visualiser son évolution via des graphiques interactifs (Chart.js) ;
- enregistrer son journal quotidien détaillé : repas, hydratation, énergie, humeur, sommeil ;
- calculer automatiquement son IMC et suivre sa progression vers son objectif de poids ;
- consulter et filtrer son historique par période (7 jours, 30 jours, 3 mois, tout) ;
- exporter ses données en fichier CSV pour un suivi externe ;
- choisir un coach certifié parmi la liste des profils disponibles ;
- envoyer une demande de suivi à un coach et suivre son statut ;
- échanger des messages avec son coach via une messagerie interne protégée par alias email ;
- recevoir des notifications in-app pour les événements importants (séance planifiée, message reçu, demande acceptée) ;
- gérer ses paramètres de confidentialité et contrôler la visibilité de son profil ;
- télécharger ses données personnelles et supprimer son compte (conformité RGPD).


### Système de rôles

FitProgress intègre trois rôles distincts avec des accès et permissions différents :

Utilisateur — gère son journal quotidien, ses objectifs, consulte ses statistiques, choisit son coach et échange avec lui via la messagerie.

Coach — suit plusieurs clients, consulte leur évolution et leurs données de santé (sans accès à leur email réel), planifie des séances, envoie des feedbacks via la messagerie, génère des liens d'invitation pour recruter de nouveaux clients et valide ou refuse leurs demandes de suivi et valide ou refuse les demandes de suivi de nouveaux clients.

Administrateur — valide les demandes de certification des coachs en vérifiant leurs certificats professionnels (BPJEPS, DEJEPS, NASM...) uploadés en PDF. Un coach ne peut apparaître dans la liste des coachs disponibles qu'après validation de son certificat par l'administrateur.

### Sécurité et confidentialité

La sécurité des données est une priorité de FitProgress :


- les mots de passe sont hashés avec l'algorithme bcrypt ;
- chaque utilisateur ne peut accéder qu'à ses propres données grâce à un système de Voters Symfony ;
- l'email réel des utilisateurs n'est jamais transmis aux coachs — un alias proxy est généré automatiquement à l'inscription ;
- tous les formulaires sont protégés contre les attaques CSRF ;
- l'application est conforme au RGPD : l'utilisateur peut à tout moment télécharger ses données ou supprimer son compte et l'intégralité des données associées.


### Stack technique


- Backend : PHP 8.2 / Symfony 7
- Base de données : MariaDB 11
- Frontend : Twig / Tailwind CSS / Chart.js
- Emails : Symfony Mailer + Mailtrap (développement)
- Upload fichiers : VichUploaderBundle (certificats PDF, photos)
- Export : League/CSV
- Infrastructure : Docker / Docker Compose
- Versioning : Git / GitHub


### Objectif

FitProgress a pour objectif d'aider les utilisateurs à prendre conscience de leur évolution physique, à mieux comprendre les facteurs qui influencent leur corps grâce au journal quotidien et à atteindre leurs objectifs de santé sur le long terme. Pour ceux qui le souhaitent, l'accompagnement optionnel d'un coach certifié permet d'aller plus loin dans la démarche.