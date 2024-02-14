# API BILMO

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/609a572e422d43978ee277feac377cbb)](https://app.codacy.com/gh/k-saidou/snowtricks/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)


# Démarrer le projet
Cloner le projet avec git clone ou télécharger le zip.

# Besoins

PHP 8.
Symfony CLI.
Composer.

Server Local (Mamp, Lamp, wamp...).
Editeur de texte (Sublime, Vs code ...).

Etape pour installer le programme:

- Démarrer son serveur local.
- Importer le fichiers SQL dans sa Database.
- Ouvrir le prjoet et installer les dépendances.


# Dépendance

- Composer Install pour générer le dossier vendor et toute les dépendances.

# server

- Depuis un terminal dans le dossier du projet, lancez la commande suivante :
symfony server:start 

# Fixtures

- Depuis un terminal dans le dossier du projet, lancez la commande suivante :
 php bin/console doctrine:fixtures:load     

# Documentation API Bilmo

- Une fois le serveur démarré, vous pouvez vous rendre sur https://127.0.0.1:8000/apip pour avoir la documentation.

# Token

- Pour obtenir un token vous pouvez utiliser un comptes de démo :

Emai
khalil.muller@gmail.com.
bullrich@gmail.com.
langosh.kaden@senger.biz.

MDP: password
- Une fois votre token obtenu, utilisez le en le passant dans le header de votre requête :

Authorization: Bearer [token]

Ou directement depuis la partie Authorize de la documentation de l'API :

Bearer [token]
